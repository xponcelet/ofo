<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Step;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use Carbon\Carbon;

class ActivityController extends Controller
{
    /** Créer une nouvelle activité liée à une étape. */
    public function store(StoreActivityRequest $request, Step $step)
    {
        $data = $request->validated();

        $normalized = $this->buildAndNormalize($data, $step);
        $normalized['step_id'] = $step->id;

        Activity::create($normalized);

        return back()->with('success', 'Activité créée avec succès.');
    }

    /** Mettre à jour une activité existante. */
    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $step = $activity->step;

        $data = $request->validated();

        // on garde le même step_id
        $normalized = $this->buildAndNormalize($data, $step);
        $normalized['step_id'] = $step->id;

        $activity->update($normalized);

        return back()->with('success', 'Activité mise à jour.');
    }

    /** Supprimer une activité. */
    public function destroy(Activity $activity)
    {
        $activity->delete();
        return back()->with('success', 'Activité supprimée.');
    }

    /**
     * Construit systématiquement start_at à partir de date + time,
     * normalise la fenêtre aux bornes de l’étape,
     * et corrige external_link.
     */
    private function buildAndNormalize(array $data, Step $step): array
    {
        // 1) Construire start_at depuis date + time (avec 09:00 par défaut si time vide)
        $startAt = null;
        $date = $data['date'] ?? null;
        $time = $data['time'] ?? null;

        if ($date) {
            $clock = $time ?: '09:00';
            $startAt = Carbon::parse("{$date} {$clock}:00")->seconds(0);
        }

        // 2) Clamp à l'intervalle de l'étape
        if ($startAt && $step->start_date && $step->end_date) {
            $min = $step->start_date->copy()->startOfDay();
            $max = $step->end_date->copy()->endOfDay();
            if ($startAt->lt($min)) $startAt = $min->copy()->setTimeFromTimeString($time ?: '09:00');
            if ($startAt->gt($max)) $startAt = $max->copy()->setTimeFromTimeString($time ?: '09:00');
        }

        // 3) Normaliser l'URL
        if (array_key_exists('external_link', $data) && !empty($data['external_link'])) {
            $url = trim((string) $data['external_link']);
            if ($url && !preg_match('~^https?://~i', $url)) {
                $url = 'https://' . ltrim($url, '/');
            }
            $data['external_link'] = $url;
        }

        // 4) Injecter les champs finaux
        $data['start_at'] = $startAt;                          // datetime|null
        $data['date']     = $startAt?->toDateString() ?? null; // cohérence

        // On n’écrit pas "time" en DB, donc on le retire pour update()
        unset($data['time']);

        return $data;
    }
}
