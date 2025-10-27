<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripUser;
use App\Models\ChecklistItem;
use App\Models\TripUserChecklistItem;
use Illuminate\Http\Request;

class TripChecklistUserController extends Controller
{
    /**
     * Affiche la checklist du voyage pour l'utilisateur connecté
     */
    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        // 🔐 Relation pivot entre user et trip
        $tripUser = TripUser::where('trip_id', $trip->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 📋 Éléments communs de la checklist
        $items = ChecklistItem::where('trip_id', $trip->id)
            ->orderBy('order')
            ->get(['id', 'trip_id', 'label', 'order', 'created_at', 'updated_at']);

        // ✅ États personnalisés de cet utilisateur
        $states = TripUserChecklistItem::where('trip_user_id', $tripUser->id)
            ->pluck('is_checked', 'checklist_item_id');

        return inertia('Trip/Checklist', [
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

        // 🔒 Vérifie que l’item appartient bien au voyage
        abort_if($item->trip_id !== $trip->id, 404, 'Élément non valide.');

        $validated = $request->validate([
            'is_checked' => ['required', 'boolean'],
        ]);

        // 🔗 Récupère le pivot TripUser (relation user-trip)
        $tripUser = TripUser::where('trip_id', $trip->id)
            ->where('user_id', auth()->id())
            ->first();

        // 🩹 Sécurité : si jamais la ligne n’existe pas (cas limite)
        if (!$tripUser) {
            $tripUser = TripUser::create([
                'trip_id' => $trip->id,
                'user_id' => auth()->id(),
                'role' => 'owner',
            ]);
        }

        //  Met à jour ou crée le statut personnel de la case
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

        return back()->with('success', 'Checklist mise à jour.');

    }
}
