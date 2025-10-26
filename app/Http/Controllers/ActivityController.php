<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Step;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller
{
    /**
     * Créer une nouvelle activité liée à une étape.
     */
    public function store(Request $request, Step $step)
    {
        $validated = $this->validateActivity($request, $step);

        // Normalisation des valeurs
        $validated = $this->normalizeActivityData($validated, $step);

        $validated['step_id'] = $step->id;
        $validated['end_at'] = null;

        Activity::create($validated);

        return back()->with('success', 'Activité créée avec succès.');
    }

    /**
     * Mettre à jour une activité existante.
     */
    public function update(Request $request, Activity $activity)
    {
        $step = $activity->step;
        $validated = $this->validateActivity($request, $step);

        $validated = $this->normalizeActivityData($validated, $step);

        $activity->update($validated);

        return back()->with('success', 'Activité mise à jour.');
    }

    /**
     * Supprimer une activité.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();
        return back()->with('success', 'Activité supprimée.');
    }

    /**
     * Validation commune entre création et mise à jour.
     */
    private function validateActivity(Request $request, Step $step)
    {
        $rules = [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'external_link' => ['nullable', 'url', 'max:255'],
            'date'          => ['nullable', 'date'],
            'start_at'      => ['nullable', 'date'],
            'cost'          => ['nullable', 'numeric', 'min:0'],
            'currency'      => ['nullable', 'string', 'max:10'],
            'category'      => ['nullable', 'string', 'max:100'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        ];

        // restreindre la date à la plage de l’étape si elle existe
        if ($step->start_date && $step->end_date) {
            $rules['date'][] = 'after_or_equal:' . $step->start_date->toDateString();
            $rules['date'][] = 'before_or_equal:' . $step->end_date->toDateString();
        }

        $validated = $request->validate($rules);

        // Convertir les champs vides en null
        array_walk($validated, function (&$v) {
            if ($v === '') $v = null;
        });

        return $validated;
    }

    /**
     * Normalisation des champs `date` et `start_at` pour cohérence.
     */
    private function normalizeActivityData(array $data, Step $step): array
    {
        $startAt = null;

        // Si start_at fourni → on parse
        if (!empty($data['start_at'])) {
            $startAt = Carbon::parse($data['start_at'])->seconds(0);
        }
        // Si pas d’heure mais une date → on ajoute 09:00 par défaut
        elseif (!empty($data['date'])) {
            $startAt = Carbon::parse($data['date'] . ' 09:00:00');
        }

        // Vérifie que la date est bien dans la plage de l’étape
        if ($startAt && $step->start_date && $step->end_date) {
            if ($startAt->lt($step->start_date->startOfDay())) {
                $startAt = $step->start_date->copy()->setTime(9, 0, 0);
            }
            if ($startAt->gt($step->end_date->endOfDay())) {
                $startAt = $step->end_date->copy()->setTime(9, 0, 0);
            }
        }

        // Mise à jour cohérente des champs
        $data['start_at'] = $startAt;
        $data['date'] = $startAt ? $startAt->toDateString() : ($data['date'] ?? null);

        return $data;
    }
}
