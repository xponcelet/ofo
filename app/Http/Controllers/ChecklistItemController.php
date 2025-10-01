<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChecklistItemController extends Controller
{
    /**
     * Affiche la page/onglet Checklist d'un voyage
     * GET /trips/{trip}/checklist-items
     */
    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        $items = $trip->checklistItems()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // Tu peux rendre une page dédiée (ex: Trips/TripChecklist.vue)
        return Inertia::render('Trips/Checklist', [
            'trip'  => $trip,
            'items' => $items,
        ]);
    }

    /**
     * Ajoute un item dans la checklist d'un voyage
     * POST /trips/{trip}/checklist-items
     */
    public function store(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        // Position à la fin
        $nextOrder = (int) $trip->checklistItems()->max('order') + 1;

        $trip->checklistItems()->create([
            'label'      => $validated['label'],
            'is_checked' => false,
            'order'      => $nextOrder,
        ]);

        return back()->with('success', 'Élément ajouté à la checklist.');
    }

    /**
     * Met à jour un item (label ou is_checked)
     * PUT/PATCH /checklist-items/{checklist_item}
     * (shallow route)
     */
    public function update(Request $request, ChecklistItem $checklist_item)
    {
        $trip = $checklist_item->trip;
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'label'      => 'sometimes|string|max:255',
            'is_checked' => 'sometimes|boolean',
            'order'      => 'sometimes|integer|min:0',
        ]);

        $checklist_item->update($validated);

        // Pour des requêtes XHR depuis Inertia, on peut simplement revenir en arrière
        return back();
    }

    /**
     * Supprime un item
     * DELETE /checklist-items/{checklist_item}
     * (shallow route)
     */
    public function destroy(ChecklistItem $checklist_item)
    {
        $trip = $checklist_item->trip;
        $this->authorize('update', $trip);

        $checklist_item->delete();

        return back()->with('success', 'Élément supprimé.');
    }

    /**
     * Toggle rapide (cocher / décocher)
     * PATCH /checklist-items/{checklist_item}/toggle
     * (route optionnelle)
     */
    public function toggle(ChecklistItem $checklist_item)
    {
        $trip = $checklist_item->trip;
        $this->authorize('update', $trip);

        $checklist_item->update([
            'is_checked' => ! $checklist_item->is_checked,
        ]);

        return back();
    }

    /**
     * Réordonner plusieurs items d'un coup
     * POST /trips/{trip}/checklist-items/reorder
     * Payload attendu:
     *   items: [
     *     { id: 12, order: 0 },
     *     { id: 15, order: 1 },
     *     ...
     *   ]
     */
    public function reorder(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $data = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.id'         => 'required|integer|exists:checklist_items,id',
            'items.*.order'      => 'required|integer|min:0',
        ]);

        // Sécurise: ne réordonne que les items appartenant à ce trip
        $ids = collect($data['items'])->pluck('id')->all();
        $items = ChecklistItem::whereIn('id', $ids)
            ->where('trip_id', $trip->id)
            ->get()
            ->keyBy('id');

        foreach ($data['items'] as $row) {
            if (isset($items[$row['id']])) {
                $items[$row['id']]->update(['order' => $row['order']]);
            }
        }

        return back()->with('success', 'Checklist réordonnée.');
    }
}
