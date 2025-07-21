<?php

namespace App\Http\Controllers;

use App\Models\Step;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccommodationController extends Controller
{
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

        Accommodation::create($validated);

        return redirect()->route('trips.show', $step->trip)->with('success', 'Logement ajouté avec succès.');
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
