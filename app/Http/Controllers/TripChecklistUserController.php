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
     * Affiche la checklist personnelle pour un voyage donné.
     * Fonctionne pour :
     *  - le propriétaire du voyage
     *  - un utilisateur ayant utilisé ce voyage (pivot trip_users)
     */
    public function index(Trip $trip)
    {
        $user = auth()->user();

        //  Vérifie que l'utilisateur a accès au voyage
        $hasAccess = (
            $trip->user_id === $user->id ||
            $trip->users()->where('user_id', $user->id)->exists()
        );

        if (! $hasAccess) {
            abort(403);
        }

        //  Récupère ou crée la relation user-trip (pivot)
        $tripUser = TripUser::firstOrCreate(
            [
                'trip_id' => $trip->id,
                'user_id' => $user->id,
            ],
            [
                'role' => $trip->user_id === $user->id ? 'owner' : 'used',
            ]
        );

        //  Récupère la checklist commune du voyage
        $items = ChecklistItem::where('trip_id', $trip->id)
            ->orderBy('order')
            ->get(['id', 'trip_id', 'label', 'order']);

        //  États personnels de l’utilisateur connecté
        $states = TripUserChecklistItem::where('trip_user_id', $tripUser->id)
            ->pluck('is_checked', 'checklist_item_id')
            ->toArray();

        return Inertia::render('Trips/Checklist', [
            'trip'   => $trip,
            'items'  => $items,
            'states' => $states,
        ]);
    }

    /**
     * Coche ou décoche un élément de checklist pour le user courant.
     * Fonctionne aussi pour les voyages inspirés.
     */
    public function toggle(Request $request, Trip $trip, ChecklistItem $item)
    {
        $user = auth()->user();

        //  Vérifie que l’utilisateur a bien accès au voyage (propriétaire ou lié via pivot)
        $hasAccess = (
            $trip->user_id === $user->id ||
            $trip->users()->where('user_id', $user->id)->exists()
        );

        if (! $hasAccess) {
            abort(403, 'Accès refusé.');
        }

        // Vérifie que l’item appartient bien à ce voyage
        abort_if($item->trip_id !== $trip->id, 404, 'Élément non valide.');

        $validated = $request->validate([
            'is_checked' => ['required', 'boolean'],
        ]);

        //  Récupère la relation pivot user-trip
        $tripUser = TripUser::firstOrCreate(
            [
                'trip_id' => $trip->id,
                'user_id' => $user->id,
            ],
            [
                'role' => $trip->user_id === $user->id ? 'owner' : 'used',
            ]
        );

        //  Met à jour ou crée l’état personnel
        $isChecked = filter_var($request->input('is_checked'), FILTER_VALIDATE_BOOLEAN);

        TripUserChecklistItem::updateOrCreate(
            [
                'trip_user_id'      => $tripUser->id,
                'checklist_item_id' => $item->id,
            ],
            [
                'is_checked' => $isChecked,
                'checked_at' => $isChecked ? now() : null,
            ]
        );

        return back()->with('success', __('Checklist mise à jour.'));
    }
}
