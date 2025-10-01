<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTripRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Trip;
use App\Models\Step;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ItineraryService;


class TripCreationController extends Controller
{
    public function destination()
    {
        return Inertia::render('Trips/Destination');
    }

    public function storeDestination(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        session([
            'destination' => $validated['destination'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return redirect()->route('trips.start')->with('success', 'Destination enregistrée.');
    }


    public function start()
    {
        return Inertia::render('Trips/Start');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'departure' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        session([
            'departure' => $validated['departure'],
            'departure_latitude' => $validated['latitude'],
            'departure_longitude' => $validated['longitude'],
        ]);

        return redirect()->route('trips.details')->with('success', 'Point de départ enregistré.');
    }


    public function details(): Response
    {
        // Optionnel : sécurité pour éviter l’accès sans les 2 étapes précédentes
        if (
            !session()->has('destination') ||
            !session()->has('departure')
        ) {
            return redirect()->route('trips.destination')->with('error', 'Veuillez compléter les étapes précédentes.');
        }

        return Inertia::render('Trips/Details');
    }


    public function finalize(StoreTripRequest $request, ItineraryService $itinerary): RedirectResponse
    {
        $destination = session('destination');
        $destinationLat = session('latitude');
        $destinationLng = session('longitude');
        $departure = session('departure');
        $departureLat = session('departure_latitude');
        $departureLng = session('departure_longitude');

        if (!$destination || !$departure || $destinationLat === null || $destinationLng === null || $departureLat === null || $departureLng === null) {
            return redirect()->route('trips.destination')->with('error', 'Les informations sont incomplètes.');
        }

        $validated = $request->validated();

        $startDate = $validated['start_date'] ?? null;
        $endDate   = $validated['end_date'] ?? null;
        $nights    = $validated['nights'] ?? null;

        // ⚡ logique de cohérence
        if ($startDate && $endDate) {
            $nights = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate));
        } elseif ($startDate && $nights !== null) {
            $endDate = \Carbon\Carbon::parse($startDate)->addDays((int) $nights)->toDateString();
        } elseif ($startDate && !$nights && !$endDate) {
            // fallback = voyage d’un jour
            $endDate = $startDate;
            $nights  = 0;
        }

        // Création du voyage
        $trip = Trip::create(array_merge(
            $validated,
            [
                'user_id'   => auth()->id(),
                'destination'=> $destination,
                'currency'  => $validated['currency'] ?? 'EUR',
                'is_public' => $request->boolean('is_public', false),
            ]
        ));

        // Étape 1 : départ
        $trip->steps()->create([
            'location'       => $departure,
            'latitude'       => $departureLat,
            'longitude'      => $departureLng,
            'order'          => 1,
            'is_departure'   => true,
            'start_date'     => $request->start_date, // = début du séjour
            'end_date'       => $request->start_date, // même jour
            'nights'         => 0,
            'transport_mode' => $request->transport_mode ?? 'car',
        ]);

        // Étape 2 : destination
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $trip->steps()->create([
            'location'       => $destination,
            'latitude'       => $destinationLat,
            'longitude'      => $destinationLng,
            'order'          => 2,
            'is_destination' => true,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'nights'         => max(0, (int) now()->parse($startDate)->diffInDays($endDate)),
            'transport_mode' => $request->transport_mode ?? 'car',
        ]);

        //  recalcul des distances/durées une fois le trip prêt
        $itinerary->recalcDistances($trip);

        // Nettoyage
        session()->forget([
            'destination', 'latitude', 'longitude',
            'departure', 'departure_latitude', 'departure_longitude',
        ]);

        return redirect()->route('trips.show', $trip)->with('success', 'Voyage créé avec succès 🎉');
    }

}
