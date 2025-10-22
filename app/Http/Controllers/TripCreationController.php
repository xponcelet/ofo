<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTripRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Trip;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ItineraryService;

class TripCreationController extends Controller
{
    /** Étape 1 : Choix de la destination */
    public function destination(): Response|RedirectResponse
    {
        $user = auth()->user();

        //  Bloque si la limite est atteinte
        if ($user && $user->trips()->count() >= $user->tripLimit()) {
            return redirect()
                ->route('trips.index')
                ->with('error', __('Vous avez atteint la limite maximale de voyages.'));
        }

        return Inertia::render('Trips/Destination');
    }

    /** Enregistre la destination en session */
    public function storeDestination(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destination'   => 'required|string|max:100',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'country'       => 'nullable|string|max:100',
            'country_code'  => 'nullable|string|size:2',
        ]);

        session([
            'destination'        => $validated['destination'],
            'latitude'           => $validated['latitude'],
            'longitude'          => $validated['longitude'],
            'country'            => $validated['country'] ?? null,
            'country_code'       => strtoupper($validated['country_code'] ?? ''),
        ]);

        return redirect()
            ->route('trips.start')
            ->with('success', __('trip_creation.destination_saved'));
    }

    /** Étape 2 : Choix du point de départ */
    public function start(): Response|RedirectResponse
    {
        $user = auth()->user();

        //  Bloque si la limite est atteinte
        if ($user && $user->trips()->count() >= $user->tripLimit()) {
            return redirect()
                ->route('trips.index')
                ->with('error', __('Vous avez atteint la limite maximale de voyages.'));
        }

        return Inertia::render('Trips/Start');
    }

    /** Enregistre le point de départ en session */
    public function store(Request $request): RedirectResponse
    {
        // Si l'utilisateur choisit de passer l'étape
        if ($request->boolean('skip', false)) {
            session()->forget([
                'departure', 'departure_latitude', 'departure_longitude',
                'departure_country', 'departure_code',
            ]);

            return redirect()
                ->route('trips.details')
                ->with('info', __('Vous pourrez définir votre point de départ plus tard.'));
        }

        // Sinon : validation normale du lieu
        $validated = $request->validate([
            'departure'      => 'nullable|string|max:100',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'country'        => 'nullable|string|max:100',
            'country_code'   => 'nullable|string|size:2',
        ]);

        session([
            'departure'           => $validated['departure'] ?? null,
            'departure_latitude'  => $validated['latitude'] ?? null,
            'departure_longitude' => $validated['longitude'] ?? null,
            'departure_country'   => $validated['country'] ?? null,
            'departure_code'      => strtoupper($validated['country_code'] ?? ''),
        ]);

        return redirect()
            ->route('trips.details')
            ->with('success', __('trip_creation.departure_saved'));
    }

    /** Étape 3 : Détails du voyage */
    public function details(): Response|RedirectResponse
    {
        $user = auth()->user();

        //  Bloque si la limite est atteinte
        if ($user && $user->trips()->count() >= $user->tripLimit()) {
            return redirect()
                ->route('trips.index')
                ->with('error', __('Vous avez atteint la limite maximale de voyages.'));
        }

        if (!session()->has('destination')) {
            return redirect()
                ->route('trips.destination')
                ->with('error', __('trip_creation.incomplete_information'));
        }

        return Inertia::render('Trips/Details');
    }

    /** Création finale du voyage */
    public function finalize(StoreTripRequest $request, ItineraryService $itinerary): RedirectResponse
    {
        $user = auth()->user();

        // 🚫 Double sécurité backend
        if ($user && $user->trips()->count() >= $user->tripLimit()) {
            return redirect()
                ->route('trips.index')
                ->with('error', __('Vous avez atteint la limite maximale de voyages.'));
        }

        // 🔹 Récupération des infos stockées
        $destination         = session('destination');
        $destinationLat      = session('latitude');
        $destinationLng      = session('longitude');
        $destinationCountry  = session('country');
        $destinationCode     = session('country_code');

        // 🟢 Départ désormais optionnel
        $departure           = session('departure') ?? null;
        $departureLat        = session('departure_latitude') ?? null;
        $departureLng        = session('departure_longitude') ?? null;
        $departureCountry    = session('departure_country') ?? null;
        $departureCode       = session('departure_code') ?? null;

        if (!$destination) {
            return redirect()
                ->route('trips.destination')
                ->with('error', __('trip_creation.incomplete_information'));
        }

        $validated = $request->validated();

        // 🔹 Calcul automatique des dates / nuits
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

        // 🔹 Création du voyage
        $trip = Trip::create([
            ...$validated,
            'user_id'     => $user->id,
            'destination' => $destination,
            'currency'    => $validated['currency'] ?? 'EUR',
            'is_public'   => $request->boolean('is_public', false),
        ]);

        // 🔹 Étape de destination (la seule créée automatiquement)
        $trip->steps()->create([
            'location'       => $destination,
            'latitude'       => $destinationLat,
            'longitude'      => $destinationLng,
            'country'        => $destinationCountry,
            'country_code'   => $destinationCode,
            'order'          => 1,
            'is_destination' => true,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'nights'         => max(0, (int) now()->parse($startDate)->diffInDays($endDate)),
            'transport_mode' => $request->transport_mode ?? 'car',
        ]);

        // 🔹 Associe l'utilisateur au voyage avec son départ (facultatif)
        $trip->users()->attach($user->id, [
            'start_location' => $departure,
            'latitude'       => $departureLat,
            'longitude'      => $departureLng,
        ]);

        // 🔹 Recalcule les distances (ItineraryService)
        $itinerary->recalcDistances($trip);

        // 🔹 Nettoie la session
        session()->forget([
            'destination', 'latitude', 'longitude', 'country', 'country_code',
            'departure', 'departure_latitude', 'departure_longitude',
            'departure_country', 'departure_code',
        ]);

        return redirect()
            ->route('trips.show', $trip)
            ->with('success', __('trip_creation.created'));
    }
}
