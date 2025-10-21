<?php

// app/Http/Controllers/StepNoteController.php
namespace App\Http\Controllers;

use App\Models\StepNote;
use App\Models\Step;
use Illuminate\Http\Request;

class StepNoteController extends Controller
{
    public function store(Request $request, Step $step)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:5000',
        ]);

        $note = StepNote::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'step_id' => $step->id,
            ],
            ['content' => $validated['content']]
        );

        return back()->with('success', 'Note enregistrée.');
    }

    public function update(Request $request, StepNote $note)
    {
        $this->authorize('update', $note);
        $validated = $request->validate(['content' => 'nullable|string|max:5000']);
        $note->update($validated);

        return back()->with('success', 'Note mise à jour.');
    }

    public function destroy(StepNote $note)
    {
        $this->authorize('delete', $note);
        $note->delete();

        return back()->with('success', 'Note supprimée.');
    }
}
