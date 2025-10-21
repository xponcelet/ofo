<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Step;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Créer une nouvelle activité liée à une étape.
     */
    public function store(Request $request, Step $step)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'external_link' => 'nullable|url|max:255',
            'date' => 'nullable|date',
            'start_at' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $validated['step_id'] = $step->id;

        Activity::create($validated);

        return back()->with('success', 'Activité créée avec succès.');
    }

    /**
     * Mettre à jour une activité existante.
     */
    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'external_link' => 'nullable|url|max:255',
            'date' => 'nullable|date',
            'start_at' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $activity->update($validated);

        return back()->with('success', 'Activité mise à jour.');
    }

    /**
     * Supprimer une activité.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return back()->with('success', 'Activité supprimée.');
    }
}
