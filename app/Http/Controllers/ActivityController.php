<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Step;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Support\Arr;

class ActivityController extends Controller
{
    public function store(StoreActivityRequest $request, Step $step): RedirectResponse
    {
        abort_unless($step->trip->user_id === auth()->id(), 403);

        Activity::create(array_merge($request->validated(), [
            'step_id' => $step->id,
        ]));

        return back()->with('success', 'Activité ajoutée.');
    }

    public function edit(Activity $activity)
    {
        abort_unless($activity->step->trip->user_id === auth()->id(), 403);

        // Étape + trip de l’activité
        $step = $activity->step()
            ->select('id','trip_id','order','location','title')
            ->with('trip:id,title,user_id')
            ->firstOrFail();

        // Étapes du même voyage (pour réassigner)
        $steps = $step->trip->steps()
            ->orderBy('order')
            ->get(['id','order','location']);

        return Inertia::render('Activities/Edit', [
            'activity' => Arr::only($activity->toArray(), [
                'id','step_id','title','description','start_at','end_at','external_link','cost','currency','category',
            ]),
            'step' => Arr::only($step->toArray(), ['id','order','location','title']),
            'steps' => $steps,
            'trip' => Arr::only($step->trip->toArray(), ['id','title']),
        ]);
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

    // (Optionnel) si tu as laissé la ressource complète:
    public function index(Step $step) {
        abort_unless($step->trip->user_id === auth()->id(), 403);
        return redirect()->to(route('steps.edit', $step).'?tab=activities');
    }
    public function create(Step $step) {
        abort_unless($step->trip->user_id === auth()->id(), 403);
        return redirect()->to(route('steps.edit', $step).'?tab=activities');
    }
    public function show(Activity $activity) {
        abort_unless($activity->step->trip->user_id === auth()->id(), 403);
        return redirect()->route('activities.edit', $activity);
    }
}
