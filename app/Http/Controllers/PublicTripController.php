<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PublicTripController extends Controller
{
    /**
     * Tableau de bord de découverte
     */
    public function dashboard(): Response
    {
        $trips = Trip::query()
            ->where('is_public', true)
            ->withCount(['favoredBy', 'steps'])
            ->with(['users:id,name', 'steps' => fn($q) => $q->where('is_destination', true)->select('id','trip_id','country','country_code')])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($trip) => $this->formatTrip($trip));

        return Inertia::render('Public/Dashboard', [
            'trips' => $trips,
        ]);
    }

    /**
     * Liste complète des voyages publics
     */
    public function index(): Response
    {
        $trips = Trip::query()
            ->where('is_public', true)
            ->withCount(['favoredBy', 'steps'])
            ->with([
                'users:id,name',
                'steps' => fn($q) => $q->where('is_destination', true)->select('id','trip_id','country','country_code'),
            ])
            ->select('id', 'title', 'description', 'image', 'start_date', 'end_date', 'is_public')
            ->latest()
            ->paginate(12)
            ->through(fn ($trip) => $this->formatTrip($trip));

        return Inertia::render('Public/Trips/Index', [
            'trips' => $trips,
        ]);
    }

    /**
     * Détail d’un voyage public
     */
    public function show(Trip $trip): Response
    {
        abort_unless($trip->is_public, 403);

        $trip->loadCount(['favoredBy', 'steps']);
        $trip->load([
            'users:id,name',
            'steps' => fn($q) => $q->orderBy('order')->select(
                'id',
                'trip_id',
                'order',
                'title',
                'description',
                'location',
                'country',
                'country_code',
                'latitude',
                'longitude',
                'is_destination',
                'start_date',
                'end_date'
            ),
        ]);

        $creator = $trip->users->first();

        return Inertia::render('Trips/Show', [
            'trip' => [
                'id'          => $trip->id,
                'title'       => $trip->title,
                'description' => $trip->description,
                'image'       => $trip->image,
                'start_date'  => $trip->start_date,
                'end_date'    => $trip->end_date,
                'favorites'   => $trip->favored_by_count,
                'is_favorite' => Auth::check()
                    ? Auth::user()->favoriteTrips->contains($trip->id)
                    : false,
                'steps'       => $trip->steps,
                'steps_count' => $trip->steps_count,
                'is_public'   => $trip->is_public,
                'creator'     => $creator ? [
                    'id'   => $creator->id,
                    'name' => $creator->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Voyage public aléatoire
     */
    public function random(): Response
    {
        $trip = Trip::query()
            ->where('is_public', true)
            ->inRandomOrder()
            ->firstOrFail();

        return redirect()->route('public.trips.show', $trip);
    }

    /**
     * Formate les données pour Inertia
     */
    private function formatTrip(Trip $trip): array
    {
        $destination = $trip->steps->firstWhere('is_destination', true);
        $creator = $trip->users->first();

        return [
            'id'          => $trip->id,
            'title'       => $trip->title,
            'description' => $trip->description,
            'image'       => $trip->image,
            'start_date'  => $trip->start_date,
            'end_date'    => $trip->end_date,
            'steps_count' => $trip->steps_count,
            'favorites'   => $trip->favored_by_count,
            'is_favorite' => Auth::check()
                ? Auth::user()->favoriteTrips->contains($trip->id)
                : false,
            'destination_country'      => $destination?->country,
            'destination_country_code' => $destination?->country_code,
            'creator' => $creator ? [
                'id'   => $creator->id,
                'name' => $creator->name,
            ] : null,
        ];
    }
}
