<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Step;
use App\Http\Requests\StepRequest;
use App\Services\ItineraryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StepController extends Controller
{
    use AuthorizesRequests;

    /** Liste des étapes d’un voyage */
    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        // Charger les étapes liées au voyage
        $steps = $trip->steps()
            ->orderBy('order')
            ->get([
                'id',
                'trip_id',
                'title',
                'location',
                'start_date',
                'end_date',
                'nights',
                'is_destination'
            ]);

        // 🔹 Nouveau : récupérer le départ utilisateur (pivot trip_user)
        $userDeparture = $trip->users()
            ->where('user_id', auth()->id())
            ->select('start_location', 'latitude', 'longitude')
            ->first();

        return Inertia::render('Steps/Index', [
            'trip' => $trip,
            'steps' => $steps,
            'userDeparture' => $userDeparture,
        ]);
    }

    /** Formulaire de création d’une étape */
    public function create(Request $request, Trip $trip)
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

    /** Création d’une étape */
    public function store(StepRequest $request, Trip $trip, ItineraryService $itinerary)
    {
        $this->authorize('update', $trip);

        $validated = $this->prepareData($request->validated());

        if (!empty($validated['country_code'])) {
            $validated['country_code'] = strtoupper($validated['country_code']);
        }

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
            $itinerary->recalcDistances($trip);
        }

        return redirect()->route('trips.show', $trip)->with('success', __('step.created'));
    }

    /** Édition d’une étape */
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

    /** Mise à jour d’une étape */
    public function update(StepRequest $request, Step $step, ItineraryService $itinerary)
    {
        $this->authorize('update', $step->trip);

        $validated = $this->prepareData($request->validated());

        if (!empty($validated['country_code'])) {
            $validated['country_code'] = strtoupper($validated['country_code']);
        }

        $step->update($validated);
        $itinerary->recalcDistances($step->trip);

        return redirect()->route('trips.show', $step->trip)->with('success', __('step.updated'));
    }

    /** Suppression */
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

    /** Duplication d’une étape */
    public function duplicate(Step $step, ItineraryService $itinerary)
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

        $newStep = $step->replicate(['order', 'created_at', 'updated_at', 'id']);
        $newStep->order = $newOrder;
        $newStep->trip_id = $trip->id;
        $newStep->is_destination = false;
        $newStep->save();

        $itinerary->recalcDistances($trip);

        return redirect()
            ->route('trips.show', $trip)
            ->with('success', __('step.duplicated'));
    }

    /** Déplacement vers le haut */
    public function moveUp(Step $step, ItineraryService $itinerary)
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
            $itinerary->recalcDistances($step->trip);
        }

        return back()->with('success', __('step.moved_up'));
    }

    /** Déplacement vers le bas */
    public function moveDown(Step $step, ItineraryService $itinerary)
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
            $itinerary->recalcDistances($step->trip);
        }

        return back()->with('success', __('step.moved_down'));
    }

    /** Ajout automatique de la date de fin si “nights” présent */
    private function prepareData(array $validated): array
    {
        if (!empty($validated['start_date']) && isset($validated['nights'])) {
            $validated['end_date'] = Carbon::parse($validated['start_date'])
                ->addDays((int) $validated['nights'])
                ->toDateString();
        }

        return $validated;
    }

    /** Réorganisation */
    public function reorder(Trip $trip)
    {
        $this->authorize('update', $trip);

        $order = request('order', []);
        foreach ($order as $index => $id) {
            Step::where('id', $id)->update(['order' => $index + 1]);
        }

        return back();
    }

    /** Mise à jour du nombre de nuits */
    public function updateNights(Request $request, Step $step)
    {
        $this->authorize('update', $step->trip);

        $data = $request->validate([
            'nights' => ['required', 'integer', 'min:0'],
        ]);

        $endDate = null;
        if (!empty($step->start_date)) {
            $start = Carbon::parse($step->start_date)->startOfDay();
            $endDate = $start->copy()->addDays($data['nights']);
        }

        $step->update([
            'nights'   => $data['nights'],
            'end_date' => $endDate,
        ]);

        return back();
    }
}
