<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChecklistItemController extends Controller
{
    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        $items = $trip->checklistItems()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return Inertia::render('Trips/Checklist', [
            'trip'  => $trip,
            'items' => $items,
        ]);
    }

    public function store(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        $nextOrder = (int) $trip->checklistItems()->max('order') + 1;

        $trip->checklistItems()->create([
            'label'      => $validated['label'],
            'is_checked' => false,
            'order'      => $nextOrder,
        ]);

        return back()->with('success', __('checklist.created'));
    }

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

        return back();
    }

    public function destroy(ChecklistItem $checklist_item)
    {
        $trip = $checklist_item->trip;
        $this->authorize('update', $trip);

        $checklist_item->delete();

        return back()->with('success', __('checklist.deleted'));
    }

    public function toggle(ChecklistItem $checklist_item)
    {
        $trip = $checklist_item->trip;
        $this->authorize('update', $trip);

        $checklist_item->update([
            'is_checked' => ! $checklist_item->is_checked,
        ]);

        return back();
    }

    public function reorder(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $data = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.id'         => 'required|integer|exists:checklist_items,id',
            'items.*.order'      => 'required|integer|min:0',
        ]);

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

        return back()->with('success', __('checklist.reordered'));
    }
}
