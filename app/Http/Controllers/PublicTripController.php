<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Step;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\TripUser;
use Illuminate\Http\Request;

class PublicTripController extends Controller
{
    /**
     * Tableau de bord de découverte (accueil public)
     */
    public function dashboard(): Response
    {
        $trips = Trip::query()
            ->where('is_public', true)
            ->withCount(['favoredBy', 'steps'])
            ->with([
                'users:id,name',
                'steps' => fn($q) => $q
                    ->where('is_destination', true)
                    ->select('id', 'trip_id', 'country', 'country_code'),
            ])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($trip) => $this->formatTrip($trip));

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
                'steps' => fn($q) => $q
                    ->where('is_destination', true)
                    ->select('id', 'trip_id', 'country', 'country_code'),
            ])
            ->select('id', 'title', 'description', 'image', 'is_public')
            ->latest()
            ->paginate(12)
            ->through(fn($trip) => $this->formatTrip($trip));

        return Inertia::render('Public/Trips/Index', [
            'trips' => $trips,
        ]);
    }

    /**
     * Détail d’un voyage public
     */
    public function show(Trip $trip): Response
    {
        if (
            ! $trip->is_public &&
            ! (auth()->check() && (
                    auth()->user()->isAdmin() ||
                    auth()->id() === $trip->user_id
                ))
        ) {
            abort(403);
        }

        $trip->loadCount(['favoredBy', 'steps']);
        $trip->load([
            'users:id,name',
            'steps' => fn($q) => $q
                ->orderBy('order')
                ->select(
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

        $tripUser = Auth::check()
            ? \App\Models\TripUser::where('user_id', Auth::id())
                ->where('trip_id', $trip->id)
                ->where('role', 'used')
                ->first()
            : null;

        return Inertia::render('Public/Trips/Show', [
            'trip' => [
                'id'          => $trip->id,
                'title'       => $trip->title,
                'description' => $trip->description,
                'image'       => $trip->image,
                'favorites'   => $trip->favored_by_count,
                'is_favorite' => Auth::check()
                    ? Auth::user()->favoriteTrips->contains($trip->id)
                    : false,
                'steps'       => $trip->steps,
                'steps_count' => $trip->steps_count,
                'start_date'  => $trip->start_date,
                'end_date'    => $trip->end_date,
                'is_public'   => $trip->is_public,
                'creator'     => $creator ? [
                    'id'   => $creator->id,
                    'name' => $creator->name,
                ] : null,
            ],
            'tripUser' => $tripUser,
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    }

    /**
     * Affiche une étape publique et ses activités
     */
    public function showStep(Step $step): Response
    {
        abort_unless($step->trip && $step->trip->is_public, 403);

        $step->load([
            'activities' => fn($q) => $q
                ->select('id', 'step_id', 'title', 'description', 'external_link'),
            'trip:id,title,is_public',
        ]);

        return Inertia::render('Public/Steps/Show', [
            'step' => [
                'id'           => $step->id,
                'trip_id'      => $step->trip_id,
                'title'        => $step->title,
                'description'  => $step->description,
                'location'     => $step->location,
                'country'      => $step->country,
                'country_code' => $step->country_code,
                'latitude'     => $step->latitude,
                'longitude'    => $step->longitude,
                'start_date'   => $step->start_date,
                'end_date'     => $step->end_date,
            ],
            'activities' => $step->activities, // 👈 séparé !
            'trip' => [
                'id'    => $step->trip->id,
                'title' => $step->trip->title,
            ],
        ]);
    }

    /**
     * Voyage public aléatoire
     */
    public function random(): RedirectResponse
    {
        $trip = Trip::query()
            ->where('is_public', true)
            ->inRandomOrder()
            ->first();

        if (! $trip) {
            return redirect()
                ->route('public.dashboard')
                ->with('error', 'Aucun voyage public disponible pour le moment.');
        }

        return redirect()->route('public.trips.show', $trip->id);
    }

    /**
     * Formatage générique d’un voyage public
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
    public function use(Request $request, Trip $trip)
    {
        abort_unless($trip->is_public, 403);

        $validated = $request->validate([
            'start_location' => ['required', 'string', 'max:255'],
            'latitude'       => ['nullable', 'numeric'],
            'longitude'      => ['nullable', 'numeric'],
            'departure_date' => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:departure_date'],
        ]);

        // Vérifier si l’utilisateur utilise déjà ce voyage
        $existing = TripUser::where('user_id', Auth::id())
            ->where('trip_id', $trip->id)
            ->where('role', 'used')
            ->first();

        if ($existing) {
            return back()->with('info', 'Vous utilisez déjà ce voyage.');
        }

        TripUser::create([
            'trip_id'        => $trip->id,
            'user_id'        => Auth::id(),
            'role'           => 'used',
            'start_location' => $validated['start_location'],
            'latitude'       => $validated['latitude'] ?? null,
            'longitude'      => $validated['longitude'] ?? null,
            'departure_date' => $validated['departure_date'],
            'end_date'       => $validated['end_date'] ?? null,
        ]);

        return redirect()
            ->route('public.trips.show', $trip->id)
            ->with('success', 'Le voyage a été ajouté à votre liste !');
    }

    public function showActivities(Step $step): Response
    {
        // Vérifie que l’étape appartient à un voyage public
        abort_unless($step->trip && $step->trip->is_public, 403);

        $step->load([
            'trip:id,title,is_public',
            'activities' => fn($q) => $q
                ->where('is_public', true)
                ->select('id', 'step_id', 'title', 'description', 'external_link', 'start_at', 'end_at'),
        ]);

        return Inertia::render('Public/Steps/Activities', [
            'step' => [
                'id'           => $step->id,
                'trip_id'      => $step->trip_id,
                'title'        => $step->title,
                'description'  => $step->description,
                'location'     => $step->location,
                'country'      => $step->country,
                'country_code' => $step->country_code,
            ],
            'activities' => $step->activities,
            'trip' => [
                'id'    => $step->trip->id,
                'title' => $step->trip->title,
            ],
        ]);
    }
}
