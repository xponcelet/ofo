<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTripRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Trip;
use App\Models\Step;
use Inertia\Inertia;
use Inertia\Response;

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
            'trip.destination' => $validated['destination'],
            'trip.latitude' => $validated['latitude'],
            'trip.longitude' => $validated['longitude'],
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
            'trip.departure' => $validated['departure'],
            'trip.departure_latitude' => $validated['latitude'],
            'trip.departure_longitude' => $validated['longitude'],
        ]);

        return redirect()->route('trips.details')->with('success', 'Point de départ enregistré.');
    }


    public function details(): Response
    {
        // Optionnel : sécurité pour éviter l’accès sans les 2 étapes précédentes
        if (
            !session()->has('trip.destination') ||
            !session()->has('trip.departure')
        ) {
            return redirect()->route('trips.destination')->with('error', 'Veuillez compléter les étapes précédentes.');
        }

        return Inertia::render('Trips/Details');
    }


    public function finalize(StoreTripRequest $request): RedirectResponse
    {
        // Récupération des données de session
        $destination = session('trip.destination');
        $destinationLat = session('trip.latitude');
        $destinationLng = session('trip.longitude');
        $departure = session('trip.departure');
        $departureLat = session('trip.departure_latitude');
        $departureLng = session('trip.departure_longitude');

        if (!$destination || !$departure || $destinationLat === null || $destinationLng === null || $departureLat === null || $departureLng === null) {
            return redirect()->route('trips.destination')->with('error', 'Les informations sont incomplètes.');
        }

        // Création du voyage
        $trip = Trip::create(array_merge(
            $request->validated(),
            [
                'user_id' => auth()->id(),
                'destination' => $destination,
                'currency' => $request->currency ?? 'EUR',
                'is_public' => $request->boolean('is_public', false),
            ]
        ));

        // Étape 1 : départ
        $trip->steps()->create([
            'location' => $departure,
            'latitude' => $departureLat,
            'longitude' => $departureLng,
            'order' => 1,
            'is_departure' => true,
        ]);

        // Étape 2 : destination
        $trip->steps()->create([
            'location' => $destination,
            'latitude' => $destinationLat,
            'longitude' => $destinationLng,
            'order' => 2,
            'is_destination' => true,
        ]);

        // Nettoyage
        session()->forget([
            'trip.destination',
            'trip.latitude',
            'trip.longitude',
            'trip.departure',
            'trip.departure_latitude',
            'trip.departure_longitude',
        ]);

        return redirect()->route('trips.edit', $trip)->with('success', 'Voyage créé avec succès 🎉');
    }

}
