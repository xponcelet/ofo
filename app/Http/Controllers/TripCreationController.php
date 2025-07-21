<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Step;
use Inertia\Inertia;

class TripCreationController extends Controller
{
    public function start()
    {
        return Inertia::render('Trips/Start');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $trip = Trip::create([
            'user_id' => auth()->id(),
            'title' => 'titre',
            'description' => null,
            'start_date' => null,
            'end_date' => null,
            'budget' => null,
            'currency' => 'EUR',
            'is_public' => false,
        ]);

        $trip->steps()->create([
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'order' => 1,
            'is_destination' => true,
        ]);

        return redirect()->route('trips.edit', $trip)->with('success', 'Destination ajoutée. Complétez votre voyage.');
    }
}
