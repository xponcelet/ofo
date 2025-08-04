<?php

namespace App\Http\Controllers;

use App\Models\Step;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class AccommodationController extends Controller
{
    use AuthorizesRequests;
    public function create(Step $step)
    {
        $this->authorize('update', $step->trip);

        return Inertia::render('Accommodations/Create', [
            'step' => $step,
        ]);
    }

    public function store(Request $request, Step $step)
    {
        $this->authorize('update', $step->trip);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'external_link' => 'nullable|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['step_id'] = $step->id;

        // Validation métier personnalisée (logement dans la période de l'étape)
        if (
            ($validated['start_date'] ?? null) &&
            ($step->start_date && $validated['start_date'] < $step->start_date)
        ) {
            return back()->withErrors(['start_date' => 'La date d’arrivée du logement ne peut pas être antérieure à celle de l’étape.'])->withInput();
        }

        if (
            ($validated['end_date'] ?? null) &&
            ($step->end_date && $validated['end_date'] > $step->end_date)
        ) {
            return back()->withErrors(['end_date' => 'La date de départ du logement ne peut pas dépasser celle de l’étape.'])->withInput();
        }


        Accommodation::create($validated);

        return redirect()->route('trips.show', $step->trip)->with('success', 'Logement ajouté avec succès.');
    }

    public function show(Accommodation $accommodation)
    {
        $this->authorize('view', $accommodation->step->trip);

        return Inertia::render('Accommodations/Show', [
            'accommodation' => $accommodation,
            'step' => $accommodation->step,
        ]);
    }


    public function edit(Accommodation $accommodation)
    {
        $this->authorize('update', $accommodation->step->trip);

        return Inertia::render('Accommodations/Edit', [
            'accommodation' => $accommodation,
            'step' => $accommodation->step,
        ]);
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        $this->authorize('update', $accommodation->step->trip);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'external_link' => 'nullable|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $accommodation->update($validated);

        return redirect()->route('trips.show', $accommodation->step->trip)->with('success', 'Logement mis à jour.');
    }

    public function destroy(Accommodation $accommodation)
    {
        $this->authorize('update', $accommodation->step->trip);

        $accommodation->delete();

        return redirect()->route('trips.show', $accommodation->step->trip)->with('success', 'Logement supprimé.');
    }
}
