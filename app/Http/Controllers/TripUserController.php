<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripUser;
use App\Models\TripUserChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TripUserController extends Controller
{
    /**
     * Met à jour le point de départ d’un utilisateur sur un voyage.
     */
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

    /**
     * Affiche un voyage inspiré (lié via trip_users)
     */
    public function showUsedTrip(TripUser $tripUser)
    {
        //  Vérifie que l’utilisateur est bien lié à ce voyage
        abort_unless($tripUser->user_id === Auth::id(), 403);

        // 🔁 Charge le voyage original avec étapes et checklist commune
        $trip = $tripUser->trip()
            ->with([
                'steps' => fn($q) => $q->orderBy('order')->with('activities'),
                'checklistItems' => fn($q) => $q->orderBy('order')->orderBy('id'),
            ])
            ->withCount('steps')
            ->firstOrFail();

        //  États personnels de l’utilisateur pour ce voyage inspiré
        $states = TripUserChecklistItem::where('trip_user_id', $tripUser->id)
            ->pluck('is_checked', 'checklist_item_id')
            ->toArray();

        // Prépare les données pour Vue
        return Inertia::render('Trips/Used/Show', [
            'trip' => [
                'id' => $trip->id,
                'title' => $trip->title,
                'description' => $trip->description,
                'image' => $trip->image,
                'is_public' => $trip->is_public,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'steps' => $trip->steps,
                'checklist_items' => $trip->checklistItems,
                'destination_country' => optional($trip->steps->firstWhere('is_destination', true))->country,
                'destination_country_code' => optional($trip->steps->firstWhere('is_destination', true))->country_code,
            ],
            'tripUser' => [
                'id' => $tripUser->id,
                'start_location' => $tripUser->start_location,
                'departure_date' => $tripUser->departure_date,
                'role' => $tripUser->role,
            ],
            'states' => $states,
        ]);
    }
}
