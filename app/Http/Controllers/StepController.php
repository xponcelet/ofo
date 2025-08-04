<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StepController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $afterId = $request->query('after');                 // ex: /trips/{trip}/steps/create?after=12
        $atStart = $request->boolean('at_start');            // ex: /trips/{trip}/steps/create?at_start=1

        // Purement indicatif pour l'UI
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
            'insert_after_id' => $afterId,   // sera renvoyé par le formulaire
            'at_start'        => $atStart,   // idem
        ]);
    }

    public function store(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'title'       => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'location'    => 'required|string',
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',

            // pilotage de l'insertion
            'insert_after_id' => 'nullable|integer|exists:steps,id',
            'at_start'        => 'nullable|boolean',
        ]);

        $insertAfterId = $request->input('insert_after_id');
        $atStart       = $request->boolean('at_start');

        DB::transaction(function () use ($trip, $insertAfterId, $atStart, &$validated) {
            if ($insertAfterId) {
                // sécurise l'appartenance au trip
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

            $trip->steps()->create($validated);
        });

        return redirect()->route('trips.show', $trip)->with('success', 'Étape ajoutée.');
    }



    public function edit(Step $step)
    {
        $this->authorize('update', $step->trip);

        $step->load('accommodations');

        return Inertia::render('Steps/Edit', [
            'step' => $step,
            'trip' => $step->trip,
            'updateUrl' => route('steps.update', $step),
        ]);
    }

    public function update(Request $request, Step $step)
    {
        $this->authorize('update', $step->trip);

        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $step->update($validated);

        return redirect()->route('trips.show', $step->trip)->with('success', 'Étape mise à jour.');
    }

    public function destroy(Step $step)
    {
        $this->authorize('update', $step->trip);

        $trip = $step->trip;

        // Supprime l'étape
        $step->delete();

        // Réajuste l'ordre des étapes restantes
        Step::where('trip_id', $trip->id)
            ->where('order', '>', $step->order)
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

        // Nouvelle position = juste avant la destination (ou fin si elle est absente)
        $newOrder = $destinationStep ? $destinationStep->order : ($trip->steps()->max('order') + 1);

        // Décaler les étapes à partir de la position cible
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

    // Pour réorganiser les étapes
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


}
