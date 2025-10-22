<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripUserController extends Controller
{
    public function updateDeparture(Request $request, Trip $trip)
    {
        $this->authorize('view', $trip);

        $validated = $request->validate([
            'start_location' => 'nullable|string|max:255',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ]);

        $trip->users()
            ->updateExistingPivot(auth()->id(), $validated);

        return back()->with('success', 'Point de départ mis à jour.');
    }
}
