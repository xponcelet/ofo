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
                'order',
                'is_destination',
            ]);

        $userDeparture = $trip->users()
            ->where('user_id', auth()->id())
            ->select('start_location', 'latitude', 'longitude')
            ->first();

        return Inertia::render('Steps/Index', [
            'trip'          => $trip,
            'steps'         => $steps,
            'userDeparture' => $userDeparture,
        ]);
    }
    /** Affichage d’une étape avec ses activités (vue détaillée) */
    public function show(Step $step)
    {
        $this->authorize('view', $step->trip);

        $step->load([
            'trip:id,user_id,title,start_date,end_date',
            'activities' => fn($q) => $q
                ->orderBy('start_at')
                ->select(
                    'id',
                    'step_id',
                    'title',
                    'description',
                    'location',
                    'latitude',
                    'longitude',
                    'start_at',
                    'end_at',
                    'external_link',
                    'cost',
                    'currency',
                    'category'
                ),
        ]);

        return Inertia::render('Steps/Show', [
            'step' => $step,
            'trip' => $step->trip,
        ]);
    }

    /** Formulaire de création d’une étape (avec date proposée) */
    public function create(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $afterId      = $request->query('after');
        $atStart      = $request->boolean('at_start');
        $defaultOrder = $trip->steps()->count() + 1;

        $afterStep = null;
        if ($afterId) {
            $afterStep = Step::where('trip_id', $trip->id)->find($afterId);
            if ($afterStep) {
                $defaultOrder = $afterStep->order + 1;
            }
        } elseif ($atStart) {
            $defaultOrder = 1;
        }

        // Calcul automatique de la start_date proposée
        $suggestedStart = null;
        if ($atStart) {
            $first = $trip->steps()->orderBy('order')->first();
            $suggestedStart = $trip->start_date
                ?? ($first?->start_date?->toDateString())
                ?? now()->toDateString();
        } elseif ($afterStep) {
            $suggestedStart = $afterStep->end_date
                ? Carbon::parse($afterStep->end_date)->toDateString()
                : ($afterStep->start_date ? Carbon::parse($afterStep->start_date)->toDateString() : null);
        }

        return Inertia::render('Steps/Create', [
            'trip'            => $trip,
            'storeUrl'        => route('trips.steps.store', $trip),
            'defaultOrder'    => $defaultOrder,
            'insert_after_id' => $afterId,
            'at_start'        => $atStart,
            'suggested_start' => $suggestedStart,
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

        // Replanification immédiate (Mode A) selon le contexte
        if ($atStart) {
            $anchor = $trip->start_date ? Carbon::parse($trip->start_date) : null;
            $itinerary->rescheduleAllFromFirst($trip, $anchor);
            $itinerary->recalcDistances($trip);
        } elseif ($insertAfterId) {
            $after = Step::where('trip_id', $trip->id)->find($insertAfterId);
            if ($after) {
                $itinerary->rescheduleFrom($after); // inclut refresh dates trip + distances
            } else {
                $itinerary->rescheduleAllFromFirst($trip);
                $itinerary->recalcDistances($trip);
            }
        } else {
            $prev = $trip->steps()->where('order', '<', $step->order)->orderBy('order', 'desc')->first();
            if ($prev) {
                $itinerary->rescheduleFrom($prev);
            } else {
                $itinerary->rescheduleAllFromFirst($trip);
                $itinerary->recalcDistances($trip);
            }
        }

        // Rediriger vers l’index des étapes
        return redirect()
            ->route('trips.steps.index', $trip)
            ->with('success', __('step.created'));
    }

    /** Édition d’une étape (avec suggested_start si vide) */
    public function edit(Step $step)
    {
        $this->authorize('update', $step->trip);

        $step->load([
            'trip:id,user_id,title,start_date,end_date',
            'accommodations:id,step_id,title,location,start_date,end_date',
            'activities' => fn($q) => $q
                ->orderBy('start_at')
                ->select('id','step_id','title','description','start_at','end_at','external_link','cost','currency','category'),
        ]);

        $allSteps = $step->trip->steps()->orderBy('order')->get(['id','order','location','start_date','end_date']);

        // suggested_start si l'étape n'a pas encore de start_date
        $suggestedStart = null;
        if (empty($step->start_date)) {
            if ($step->order === 1) {
                $suggestedStart = $step->trip->start_date
                    ? $step->trip->start_date->toDateString()
                    : now()->toDateString();
            } else {
                $prev = $step->trip->steps()->where('order', $step->order - 1)->first();
                if ($prev) {
                    $suggestedStart = $prev->end_date
                        ? Carbon::parse($prev->end_date)->toDateString()
                        : ($prev->start_date ? Carbon::parse($prev->start_date)->toDateString() : now()->toDateString());
                }
            }
        }

        return Inertia::render('Steps/Edit', [
            'step'            => $step,
            'trip'            => $step->trip,
            'allSteps'        => $allSteps,
            'suggested_start' => $suggestedStart,
        ]);
    }

    /** Mise à jour d’une étape (replanifie si start/nights changent) */
    public function update(StepRequest $request, Step $step, ItineraryService $itinerary)
    {
        $this->authorize('update', $step->trip);

        $originalStart  = $step->start_date ? $step->start_date->toDateString() : null;
        $originalNights = $step->nights;

        $validated = $this->prepareData($request->validated());

        if (!empty($validated['country_code'])) {
            $validated['country_code'] = strtoupper($validated['country_code']);
        }

        $step->fill($validated);

        $changedStart  = array_key_exists('start_date', $validated)
            ? (($validated['start_date'] ?? null) !== $originalStart)
            : false;
        $changedNights = array_key_exists('nights', $validated)
            ? ((int)($validated['nights'] ?? null) !== (int)$originalNights)
            : false;

        $step->save();

        if ($changedStart || $changedNights) {
            // Cascade autoritaire depuis l’étape modifiée
            $itinerary->rescheduleFrom($step); // inclut refresh dates trip + distances
        } else {
            // Sinon au moins distances si coords/transport ont changé
            $itinerary->recalcDistances($step->trip);
        }

        // Retourner sur l’index des étapes
        return redirect()->route('trips.steps.index', $step->trip)->with('success', __('step.updated'));
    }

    /** Suppression (≥ 1 étape + destination non-supprimable) */
    public function destroy(Step $step, ItineraryService $itinerary)
    {
        $this->authorize('update', $step->trip);

        $trip = $step->trip;

        if ($trip->steps()->count() <= 1) {
            return back()->with('error', "Vous devez conserver au moins une étape dans ce voyage.");
        }
        if ($step->is_destination) {
            return back()->with('error', "La destination finale ne peut pas être supprimée.");
        }

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

        $anchor = $trip->start_date ? Carbon::parse($trip->start_date) : null;
        $itinerary->rescheduleAllFromFirst($trip, $anchor);
        $itinerary->recalcDistances($trip);

        return back()->with('success', __('step.deleted'));
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

    /** Ajout automatique de la date de fin si “nights” présent (pour store/update classiques) */
    private function prepareData(array $validated): array
    {
        if (!empty($validated['start_date']) && isset($validated['nights'])) {
            $validated['end_date'] = Carbon::parse($validated['start_date'])
                ->addDays((int) $validated['nights'])
                ->toDateString();
        }

        return $validated;
    }

    /** Réorganisation (DnD) avec replanification immédiate */
    public function reorder(Trip $trip, Request $request, ItineraryService $itinerary)
    {
        $this->authorize('update', $trip);

        $order = $request->input('order', []);
        foreach ($order as $index => $id) {
            Step::where('id', $id)->where('trip_id', $trip->id)->update(['order' => $index + 1]);
        }

        $itinerary->rescheduleAllFromFirst($trip);
        $itinerary->recalcDistances($trip);

        return back();
    }

    /** Mise à jour du nombre de nuits (cascade autoritaire) */
    public function updateNights(Request $request, Step $step, ItineraryService $itinerary)
    {
        $this->authorize('update', $step->trip);

        $data = $request->validate([
            'nights' => ['required', 'integer', 'min:0'],
        ]);

        $step->nights = (int) $data['nights'];
        $step->save();

        $itinerary->rescheduleFrom($step);

        return back();
    }

    /** Mise à jour de la date de départ d’une étape (cascade autoritaire) */
    public function updateStartDate(Request $request, Step $step, ItineraryService $itinerary)
    {
        $this->authorize('update', $step->trip);

        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $step->start_date = Carbon::parse($data['start_date'])->startOfDay();
        $step->save();

        $itinerary->rescheduleFrom($step);

        return back();
    }

    /** Replanifier tout le trip depuis la 1ʳᵉ étape (ou ancre fournie) */
    public function rebuildSchedule(Request $request, Trip $trip, ItineraryService $itinerary)
    {
        $this->authorize('update', $trip);

        $data = $request->validate([
            'trip_start' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $anchor = !empty($data['trip_start'])
            ? Carbon::parse($data['trip_start'])->startOfDay()
            : null;

        $itinerary->rescheduleAllFromFirst($trip, $anchor);
        $itinerary->recalcDistances($trip);

        return back()->with('success', __('trip.schedule_rebuilt'));
    }
}
