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

        // Validation métier personnalisée
        if (
            ($validated['start_date'] ?? null) &&
            ($step->start_date && $validated['start_date'] < $step->start_date)
        ) {
            return back()->withErrors([
                'start_date' => __('accommodation.errors.start_before_step'),
            ])->withInput();
        }

        if (
            ($validated['end_date'] ?? null) &&
            ($step->end_date && $validated['end_date'] > $step->end_date)
        ) {
            return back()->withErrors([
                'end_date' => __('accommodation.errors.end_after_step'),
            ])->withInput();
        }

        Accommodation::create($validated);

        return redirect()
            ->route('trips.show', $step->trip)
            ->with('success', __('accommodation.created'));
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

        return redirect()
            ->route('trips.show', $accommodation->step->trip)
            ->with('success', __('accommodation.updated'));
    }

    public function destroy(Accommodation $accommodation)
    {
        $this->authorize('update', $accommodation->step->trip);

        $accommodation->delete();

        return redirect()
            ->route('trips.show', $accommodation->step->trip)
            ->with('success', __('accommodation.deleted'));
    }
}
