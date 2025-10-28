<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripUser;
use App\Models\ChecklistItem;
use App\Models\TripUserChecklistItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripChecklistUserController extends Controller
{
    /**
     * Affiche la checklist du voyage pour l'utilisateur connecté
     */
    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        // 🔗 Récupère la relation user-trip
        $tripUser = TripUser::where('trip_id', $trip->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 📋 Récupère les items de la checklist du voyage
        $items = ChecklistItem::where('trip_id', $trip->id)
            ->orderBy('order')
            ->get(['id', 'trip_id', 'label', 'order']);

        // ✅ États personnels de l’utilisateur connecté
        $states = TripUserChecklistItem::where('trip_user_id', $tripUser->id)
            ->pluck('is_checked', 'checklist_item_id');

        return Inertia::render('Trips/Checklist', [
            'trip'   => $trip,
            'items'  => $items,
            'states' => $states,
        ]);
    }

    /**
     * Coche ou décoche un élément pour l'utilisateur connecté
     */
    public function toggle(Request $request, Trip $trip, ChecklistItem $item)
    {
        $this->authorize('view', $trip);

        // 🔒 Vérifie que l’item appartient bien à ce voyage
        abort_if($item->trip_id !== $trip->id, 404, 'Élément non valide.');

        $validated = $request->validate([
            'is_checked' => ['required', 'boolean'],
        ]);

        // 🔗 Relation pivot (user-trip)
        $tripUser = TripUser::firstOrCreate(
            [
                'trip_id' => $trip->id,
                'user_id' => auth()->id(),
            ],
            [
                'role' => 'owner', // par défaut si non défini
            ]
        );

        // ✅ Met à jour ou crée le statut personnel
        TripUserChecklistItem::updateOrCreate(
            [
                'trip_user_id'      => $tripUser->id,
                'checklist_item_id' => $item->id,
            ],
            [
                'is_checked' => $validated['is_checked'],
                'checked_at' => $validated['is_checked'] ? now() : null,
            ]
        );

        // 🔁 Redirection propre sans AJAX (Inertia fera le reload du composant)
        return back()->with('success', __('Checklist mise à jour.'));
    }
}
