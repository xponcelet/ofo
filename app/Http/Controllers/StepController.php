<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Step;
use App\Services\MapboxService;
use App\Http\Requests\StepRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class StepController extends Controller
{
    use AuthorizesRequests;

    public function create(\Illuminate\Http\Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $afterId = $request->query('after');
        $atStart = $request->boolean('at_start');
        $defaultOrder = $trip->steps()->count() + 1;

        if ($afterId) {
            $afterStep = Step::where('trip_id', $trip->id)->find($afterId);
            if ($afterStep) {
                $defaultOrder = $afterStep->order + 1;
            }
        } elseif ($atStart) {
            $defaultOrder = 1;
        }

        return Inertia::render('Steps/Create', [
            'trip'            => $trip,
            'storeUrl'        => route('trips.steps.store', $trip),
            'defaultOrder'    => $defaultOrder,
            'insert_after_id' => $afterId,
            'at_start'        => $atStart,
        ]);
    }

    public function store(StepRequest $request, Trip $trip, MapboxService $mapbox)
    {
        $this->authorize('update', $trip);

        $validated = $this->prepareData($request->validated());

        $insertAfterId = $request->input('insert_after_id');
        $atStart       = $request->boolean('at_start');

        $step = null;

        DB::transaction(function () use ($trip, $insertAfterId, $atStart, &$validated, &$step) {
            if ($insertAfterId) {
                $after = Step::where('trip_id', $trip->id)->findOrFail($insertAfterId);

                Step::where('trip_id', $trip->id)
                    ->where('order', '>', $after->order)
                    ->increment('order');

                $validated['order'] = $after->order + 1;
            } elseif ($atStart) {
                Step::where('trip_id', $trip->id)->increment('order');
                $validated['order'] = 1;
            } else {
                $maxOrder = Step::where('trip_id', $trip->id)->max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            $step = $trip->steps()->create($validated);
        });

        if ($step) {
            $this->updateDistances($trip, $mapbox);
        }

        return redirect()->route('trips.show', $trip)->with('success', 'Étape ajoutée.');
    }

    public function edit(Step $step)
    {
        $this->authorize('update', $step->trip);

        $step->load([
            'trip:id,user_id,title',
            'accommodations:id,step_id,title,location,start_date,end_date',
            'activities' => fn($q) => $q
                ->orderBy('start_at')
                ->select('id','step_id','title','description','start_at','end_at','external_link','cost','currency','category'),
        ]);

        $allSteps = $step->trip->steps()->orderBy('order')->get(['id','order','location']);

        return Inertia::render('Steps/Edit', [
            'step'     => $step,
            'trip'     => $step->trip,
            'allSteps' => $allSteps,
        ]);
    }

    public function update(StepRequest $request, Step $step, MapboxService $mapbox)
    {
        $this->authorize('update', $step->trip);

        $validated = $this->prepareData($request->validated());

        $step->update($validated);

        $this->updateDistances($step->trip, $mapbox);

        return redirect()->route('trips.show', $step->trip)->with('success', 'Étape mise à jour.');
    }

    public function destroy(Step $step)
    {
        $this->authorize('update', $step->trip);

        $trip = $step->trip;
        $deletedOrder = $step->order;

        $step->delete();

        Step::where('trip_id', $trip->id)
            ->where('order', '>', $deletedOrder)
            ->orderBy('order')
            ->get()
            ->each(function ($s) {
                $s->order = $s->order - 1;
                $s->save();
            });

        return redirect()->route('trips.show', $trip)->with('success', 'Étape supprimée et ordre mis à jour.');
    }

    public function duplicate(Step $step)
    {
        $this->authorize('update', $step->trip);

        $trip = $step->trip;
        $destinationStep = $trip->steps()->where('is_destination', true)->first();

        $newOrder = $destinationStep ? $destinationStep->order : ($trip->steps()->max('order') + 1);

        Step::where('trip_id', $trip->id)
            ->where('order', '>=', $newOrder)
            ->orderBy('order', 'desc')
            ->get()
            ->each(function ($s) {
                $s->order++;
                $s->save();
            });

        $newStep = $step->replicate([
            'order', 'created_at', 'updated_at', 'id'
        ]);

        $newStep->order = $newOrder;
        $newStep->trip_id = $trip->id;
        $newStep->is_destination = false;
        $newStep->save();

        return redirect()
            ->route('trips.show', $trip)
            ->with('success', 'Étape dupliquée avec succès.');
    }

    public function moveUp(Step $step)
    {
        $this->authorize('update', $step->trip);

        $previous = Step::where('trip_id', $step->trip_id)
            ->where('order', '<', $step->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previous) {
            [$step->order, $previous->order] = [$previous->order, $step->order];
            $step->save();
            $previous->save();
        }

        return back()->with('success', 'Étape déplacée vers le haut.');
    }

    public function moveDown(Step $step)
    {
        $this->authorize('update', $step->trip);

        $next = Step::where('trip_id', $step->trip_id)
            ->where('order', '>', $step->order)
            ->orderBy('order')
            ->first();

        if ($next) {
            [$step->order, $next->order] = [$next->order, $step->order];
            $step->save();
            $next->save();
        }

        return back()->with('success', 'Étape déplacée vers le bas.');
    }

    /**
     * Applique la logique de calcul des champs automatiques
     */
    private function prepareData(array $validated): array
    {
        if (!empty($validated['start_date']) && isset($validated['nights'])) {
            $validated['end_date'] = Carbon::parse($validated['start_date'])
                ->addDays((int) $validated['nights'])
                ->toDateString();
        }

        return $validated;
    }

    /**
     * Recalcule les distances/durées pour toutes les étapes du trip
     */
    private function updateDistances(Trip $trip, MapboxService $mapbox): void
    {
        $steps = $trip->steps()->orderBy('order')->get();

        foreach ($steps as $index => $step) {
            $next = $steps->get($index + 1);
            if (!$next) {
                $step->update([
                    'distance_to_next' => null,
                    'duration_to_next' => null,
                ]);
                continue;
            }

            if ($step->latitude && $step->longitude && $next->latitude && $next->longitude) {
                $result = $mapbox->getDistanceAndDuration(
                    $step->latitude, $step->longitude,
                    $next->latitude, $next->longitude,
                    $step->transport_mode ?? 'driving'
                );

                if ($result) {
                    $step->update([
                        'distance_to_next' => $result['distance'],
                        'duration_to_next' => $result['duration'],
                    ]);
                }
            }
        }
    }
}
