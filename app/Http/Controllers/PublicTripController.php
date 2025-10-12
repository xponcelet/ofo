<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PublicTripController extends Controller
{
    /**
     * Liste des voyages publics
     */
    public function index(Request $request): Response
    {
        $query = Trip::query()
            ->isPublic()
            ->withCount(['steps'])
            ->with(['steps:id,trip_id,is_destination,country,country_code'])
            ->when($request->filled('q'), fn($q) =>
            $q->where('title', 'like', "%{$request->q}%")
                ->orWhere('description', 'like', "%{$request->q}%")
            )
            ->when($request->sort === 'oldest', fn($q) => $q->oldest())
            ->when($request->sort === 'title', fn($q) => $q->orderBy('title'))
            ->when(!in_array($request->sort, ['oldest', 'title']), fn($q) => $q->latest());

        $trips = $query->paginate($request->integer('perPage', 12))
            ->through(function ($trip) {
                $destinationStep = $trip->steps->firstWhere('is_destination', true);

                return [
                    'id'           => $trip->id,
                    'title'        => $trip->title,
                    'description'  => $trip->description,
                    'image'        => $trip->image,
                    'days_count'   => $trip->days_count,
                    'steps_count'  => $trip->steps_count,
                    'destination_country'      => $destinationStep?->country,
                    'destination_country_code' => $destinationStep?->country_code,
                ];
            });

        return Inertia::render('Public/Trips/Index', [
            'trips' => $trips,
            'filters' => [
                'q' => $request->q,
                'sort' => $request->sort ?? 'latest',
            ],
        ]);
    }

    /**
     * Détail d’un voyage public
     */
    public function show(Trip $trip): Response
    {
        abort_unless($trip->is_public, 404);

        $trip->load([
            'steps' => function ($q) {
                $q->orderBy('order')->select(
                    'id',
                    'trip_id',
                    'order',
                    'title',
                    'description',
                    'location',
                    'country_code',
                    'country',
                    'start_date',
                    'end_date',
                    'latitude',
                    'longitude',
                    'nights',
                    'distance_to_next',
                    'duration_to_next'
                );
            },
            'steps.activities:id,step_id,title,description,location,start_at,end_at,external_link,cost,currency,category',
        ]);

        // 🧭 Toutes les activités du voyage
        $activities = $trip->steps->flatMap(function ($step) {
            return $step->activities->map(function ($a) use ($step) {
                return [
                    'id'            => $a->id,
                    'step_id'       => $a->step_id,
                    'title'         => $a->title,
                    'description'   => $a->description,
                    'location'      => $a->location,
                    'start_at'      => optional($a->start_at)->toDateTimeString(),
                    'end_at'        => optional($a->end_at)->toDateTimeString(),
                    'date'          => optional($a->start_at)->toDateString(),
                    'step_location' => $step->location,
                    'step_title'    => $step->title,
                ];
            });
        })->values();

        // 🗓️ Génère la liste complète des jours
        $days = [];
        if ($trip->start_date && $trip->end_date) {
            $period = new \DatePeriod(
                new \DateTime($trip->start_date),
                new \DateInterval('P1D'),
                (new \DateTime($trip->end_date))->modify('+1 day')
            );

            foreach ($period as $date) {
                $formatted = $date->format('Y-m-d');

                // Trouve la step correspondant à ce jour
                $dayStep = $trip->steps->first(function ($step) use ($date) {
                    return $date >= new \DateTime($step->start_date)
                        && $date <= new \DateTime($step->end_date);
                });

                // Récupère les activités de ce jour
                $dayActivities = $activities->filter(fn($a) => $a['date'] === $formatted)->values();

                $days[] = [
                    'date'        => $formatted,
                    'location'    => $dayStep?->location,
                    'step_id'     => $dayStep?->id,
                    'activities'  => $dayActivities,
                ];
            }
        }

        return Inertia::render('Public/Trips/Show', [
            'trip' => [
                'id'             => $trip->id,
                'title'          => $trip->title,
                'description'    => $trip->description,
                'image'          => $trip->image,
                'days_count'     => $trip->days_count,
                'steps_count'    => $trip->steps_count,
                'steps'          => $trip->steps,
                'destination_country_code' => $trip->steps->firstWhere('is_destination', true)?->country_code,
            ],
            'activities' => $activities,
            'totalActivitiesCount' => $activities->count(),
            'days' => $days,
        ]);
    }


    /**
     * Redirige vers un voyage public aléatoire
     */
    public function random(): RedirectResponse
    {
        $trip = Trip::query()
            ->where('is_public', true)
            ->inRandomOrder()
            ->firstOrFail();

        return redirect()->route('public.trips.show', $trip);
    }
}
