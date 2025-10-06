<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Step;
use App\Http\Requests\StoreActivityRequest;

class ActivityController extends Controller
{
    /**
     * Créer une nouvelle activité liée à une étape.
     */
    public function store(StoreActivityRequest $request, Step $step)
    {
        $data = $request->validated();
        $data['step_id'] = $step->id;

        Activity::create($data);

        return back()->with('success', 'Activité créée avec succès.');
    }

    /**
     * Mettre à jour une activité existante.
     */
    public function update(StoreActivityRequest $request, Activity $activity)
    {
        $activity->update($request->validated());

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
