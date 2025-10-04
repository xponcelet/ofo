<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Step;
use App\Http\Requests\StepRequest;
use App\Services\ItineraryService;
use App\Services\GeocodingService;
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

    public function store(StepRequest $request, Trip $trip, ItineraryService $itinerary, GeocodingService $geo)
    {
        $this->authorize('update', $trip);

        $validated = $this->prepareData($request->validated());

        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $validated['country'] = $geo->getCountryCode(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
        }

        $insertAfterId = $request->input('insert_after_id');
        $atStart = $request->boolean('at_start');

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
            $itinerary->recalcDistances($trip);
        }

        return redirect()->route('trips.show', $trip)->with('success', __('step.created'));
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

    public function update(StepRequest $request, Step $step, ItineraryService $itinerary, GeocodingService $geo)
    {
        $this->authorize('update', $step->trip);

        $validated = $this->prepareData($request->validated());

        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $validated['country'] = $geo->getCountryCode(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
        }

        $step->update($validated);

        $itinerary->recalcDistances($step->trip);

        return redirect()->route('trips.show', $step->trip)->with('success', __('step.updated'));
    }

    public function destroy(Step $step, ItineraryService $itinerary)
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

        $itinerary->recalcDistances($trip);

        return redirect()->route('trips.show', $trip)->with('success', __('step.deleted'));
    }

    private function prepareData(array $validated): array
    {
        if (!empty($validated['start_date']) && isset($validated['nights'])) {
            $validated['end_date'] = Carbon::parse($validated['start_date'])
                ->addDays((int) $validated['nights'])
                ->toDateString();
        }

        return $validated;
    }
}
