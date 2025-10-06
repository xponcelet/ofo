<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use DateTime;
class TripController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request): \Inertia\Response
    {
        // 🧩 Si l’utilisateur n’est pas connecté → page d’invitation
        if (!auth()->check()) {
            return \Inertia\Inertia::render('Trips/GuestPlaceholder');
        }

        // 🧍‍♂️ Récupération de l’utilisateur connecté
        $user = auth()->user();
        $perPage = (int) $request->integer('per_page', 12);

        // 🌍 Récupération des voyages de l’utilisateur
        $trips = \App\Models\Trip::query()
            ->where('user_id', $user->id)
            ->withCount(['steps', 'favoredBy as favs'])
            ->with([
                'steps:id,trip_id,start_date,end_date,nights,is_destination,country,country_code'
            ])
            ->latest()
            ->paginate($perPage)
            ->through(function ($trip) {
                $destinationStep = $trip->steps->firstWhere('is_destination', true);

                return [
                    'id'           => $trip->id,
                    'title'        => $trip->title,
                    'description'  => $trip->description,
                    'image'        => $trip->image,
                    'is_public'    => $trip->is_public,
                    'favs'         => $trip->favs,
                    'start_date'   => $trip->start_date,
                    'end_date'     => $trip->end_date,
                    'total_nights' => $trip->total_nights,
                    'days_count'   => $trip->days_count,
                    'steps_count'  => $trip->steps_count,
                    'destination_country'      => $destinationStep?->country,
                    'destination_country_code' => $destinationStep?->country_code,
                ];
            });

        // 🔢 Infos sur la limite de voyages
        $count = \App\Models\Trip::where('user_id', $user->id)->count();
        $max   = $user->tripLimit();

        return \Inertia\Inertia::render('Trips/Index', [
            'trips' => $trips,
            'limits' => [
                'max'   => $max,
                'count' => $count,
            ],
            'can' => [
                'create_trip' => $count < $max,
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        $response = Gate::inspect('create', Trip::class);

        if (! $response->allowed()) {
            return Inertia::render('Trips/Index', [
                'trips' => Trip::where('user_id', auth()->id())
                    ->select(['id','title','description','image'])
                    ->withCount(['steps','favoredBy as favs'])
                    ->latest()->paginate(12),
                'limits' => [
                    'max'   => auth()->user()->tripLimit(),
                    'count' => auth()->user()->trips()->count(),
                ],
                'errors' => [
                    'base' => __('trip.limit_reached', [
                        'max' => auth()->user()->tripLimit(),
                    ]),
                ],
            ]);
        }

        return Inertia::render('Trips/Create');
    }

    public function store(StoreTripRequest $request)
    {
        $user = auth()->user();
        $lock = Cache::lock("user:{$user->id}:create-trip", 5);

        return $lock->block(5, function () use ($request, $user) {
            $this->authorize('create', Trip::class);

            $validated = $request->validated();

            $startDate = $validated['start_date'] ?? null;
            $endDate   = $validated['end_date'] ?? null;
            $nights    = $validated['nights'] ?? null;

            if ($startDate && $endDate) {
                $nights = \Carbon\Carbon::parse($startDate)
                    ->diffInDays(\Carbon\Carbon::parse($endDate));
            } elseif ($startDate && $nights !== null) {
                $endDate = \Carbon\Carbon::parse($startDate)
                    ->addDays((int) $nights)
                    ->toDateString();
            } elseif ($startDate && !$nights && !$endDate) {
                $endDate = $startDate;
                $nights  = 0;
            }

            $trip = Trip::create([
                'user_id'     => $user->id,
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image'       => null,
                'budget'      => $validated['budget'] ?? null,
                'currency'    => $validated['currency'] ?? 'EUR',
                'is_public'   => (bool) ($validated['is_public'] ?? false),
            ]);

            if ($start = session('start')) {
                $trip->steps()->create([
                    'title'          => __('trip.departure'),
                    'location'       => $start,
                    'order'          => 1,
                    'is_destination' => false,
                    'start_date'     => $startDate,
                    'end_date'       => $startDate,
                    'nights'         => 0,
                ]);
            }

            if ($destination = session('destination')) {
                $trip->steps()->create([
                    'title'          => $validated['title'] ?? $destination,
                    'location'       => $destination,
                    'order'          => 2,
                    'is_destination' => true,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'nights'         => $nights,
                    'transport_mode' => $validated['transport_mode'] ?? 'car',
                ]);
            }

            return redirect()
                ->route('trips.show', $trip)
                ->with('success', __('trip.created'));
        });
    }

    public function show(Trip $trip): InertiaResponse
    {
        $this->authorize('view', $trip);

        $trip->loadCount(['favoredBy as favs', 'steps']);

        $likers = null;
        if (auth()->check() && auth()->user()->can('viewLikers', $trip)) {
            $likers = $trip->favoredBy()
                ->select('users.id', 'users.name', 'users.email')
                ->withPivot('created_at')
                ->orderByDesc('favorites.created_at')
                ->paginate(12)
                ->withQueryString();
        }

        $trip->load([
            'steps' => function ($q) {
                $q->orderBy('order')->select(
                    'id',
                    'trip_id',
                    'order',
                    'title',
                    'location',
                    'start_date',
                    'end_date',
                    'latitude',
                    'longitude',
                    'nights'
                );
            },
            'steps.activities' => function ($q) {
                $q->orderBy('start_at')->select(
                    'id',
                    'step_id',
                    'title',
                    'description',
                    'location',
                    'start_at',
                    'end_at',
                    'external_link',
                    'cost',
                    'currency',
                    'category'
                );
            },
            'steps.accommodations' => function ($q) {
                $q->select('id', 'step_id', 'title', 'location', 'start_date', 'end_date');
            },
            'checklistItems' => fn($q) => $q->orderBy('order')->orderBy('id'),
        ]);

        // 🧭 Rassemble toutes les activités
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
                    'external_link' => $a->external_link,
                    'cost'          => $a->cost,
                    'currency'      => $a->currency,
                    'category'      => $a->category,
                    'date'          => optional($a->start_at)->toDateString(),
                    'step_location' => $step->location,
                    'step_title'    => $step->title,
                ];
            });
        })->values();

        // 🗓️ Génère la liste complète des jours
        $days = [];
        if ($trip->start_date && $trip->end_date) {
            $period = new DatePeriod(
                new DateTime($trip->start_date),
                new DateInterval('P1D'),
                (new DateTime($trip->end_date))->modify('+1 day')
            );

            foreach ($period as $date) {
                $dayStep = $trip->steps->first(function ($step) use ($date) {
                    return $date >= new DateTime($step->start_date)
                        && $date <= new DateTime($step->end_date);
                });

                $days[] = [
                    'date'      => $date->format('Y-m-d'),
                    'location'  => $dayStep->location ?? null,
                    'step_id'   => $dayStep->id ?? null,
                    'step'      => $dayStep ?? null,
                ];
            }
        }

        // 🎒 Données du voyage
        $tripData = [
            'id'             => $trip->id,
            'title'          => $trip->title,
            'description'    => $trip->description,
            'image'          => $trip->image,
            'is_public'      => $trip->is_public,
            'favs'           => $trip->favs,
            'start_date'     => $trip->start_date,
            'end_date'       => $trip->end_date,
            'total_nights'   => $trip->total_nights,
            'days_count'     => $trip->days_count,
            'steps_count'    => $trip->steps_count,
            'steps'          => $trip->steps,
            'checklist_items'=> $trip->checklistItems,
            'days'           => $days, // ✅ ajout ici
        ];

        return Inertia::render('Trips/Show', [
            'trip'       => $tripData,
            'steps'      => $trip->steps,
            'activities' => $activities,
            'favs'       => $trip->favs,
            'likers'     => $likers,
        ]);
    }

    public function edit(Trip $trip): InertiaResponse
    {
        $this->authorize('update', $trip);

        return Inertia::render('Trips/Edit', [
            'trip' => $trip,
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $trip->update($request->validated());

        return redirect()
            ->route('trips.show', $trip)
            ->with('success', __('trip.updated'));
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();

        return redirect()->route('trips.index')->with('success', __('trip.deleted'));
    }

    public function details(Request $request): InertiaResponse
    {
        $this->authorize('create', Trip::class);

        $destination = session('destination');
        $start       = session('start');

        if (!$destination) {
            return redirect()->route('trips.create')
                ->with('error', __('trip.choose_destination_first'));
        }

        return Inertia::render('Trips/Details', [
            'destination' => $destination,
            'start'       => $start,
        ]);
    }
}
