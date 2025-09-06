<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Step;
use Illuminate\Http\RedirectResponse;

class ActivityController extends Controller
{
    public function store(StoreActivityRequest $request, Step $step): RedirectResponse
    {
        abort_unless($step->trip->user_id === auth()->id(), 403);

        Activity::create(array_merge(
            $request->validated(),
            ['step_id' => $step->id] // imposé par l’URL
        ));

        return back()->with('success', 'Activité ajoutée.');
    }

    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        abort_unless($activity->step->trip->user_id === auth()->id(), 403);

        $activity->update($request->validated());

        return back()->with('success', 'Activité mise à jour.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        abort_unless($activity->step->trip->user_id === auth()->id(), 403);

        $activity->delete();

        return back()->with('success', 'Activité supprimée.');
    }
}
