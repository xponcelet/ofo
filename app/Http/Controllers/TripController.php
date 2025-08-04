<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class TripController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $trips = Trip::where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
        ]);
    }

    public function create()
    {
        return Inertia::render('Trips/Create');
    }

    public function store(StoreTripRequest $request)
    {
        $validated = $request->validated();

        $trip = Trip::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image' => null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'currency' => $validated['currency'] ?? 'EUR',
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return redirect()->route('trips.show', $trip)->with('success', 'Voyage créé avec succès.');
    }

    public function show(Trip $trip)
    {
        $this->authorize('view', $trip); // facultatif si politique de sécurité

        $trip->load('steps'); // on charge les étapes liées
        $trip->load('steps.accommodations'); // on charge les logements liés aux logements


        return Inertia::render('Trips/Show', [
            'trip' => $trip,
            'steps' => $trip->steps()->select('id', 'title', 'location', 'latitude', 'longitude')->get(),
        ]);
    }

    public function edit(Trip $trip)
    {
        $this->authorize('update', $trip);

        return Inertia::render('Trips/Edit', [
            'trip' => $trip,
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validated();

        $trip->update($validated);

        return redirect()->route('trips.show', $trip)->with('success', 'Voyage mis à jour avec succès.');
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);

        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Voyage supprimé.');
    }
}

