<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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

    public function showUsedTrip(TripUser $tripUser)
    {
        // 🔒 Vérification d’accès
        abort_unless($tripUser->user_id === Auth::id(), 403);

        // On charge le voyage original et ses étapes
        $trip = $tripUser->trip()->with([
            'steps.activities',
            'checklistItems',
        ])->firstOrFail();

        return Inertia::render('Trips/Used/Show', [
            'trip' => $trip,
            'tripUser' => $tripUser,
        ]);
    }
}
