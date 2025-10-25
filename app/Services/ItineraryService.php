<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Step;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItineraryService
{
    public function __construct(private MapboxService $mapbox) {}

    /** --------- Profils Mapbox --------- */
    private function mapTransportMode(?string $mode): string
    {
        return match ($mode) {
            'car'  => 'driving',
            'bike' => 'cycling',
            'walk' => 'walking',
            default => 'driving',
        };
    }

    /** --------- Distances / Durées (existant) --------- */
    public function recalcDistances(Trip $trip): void
    {
        $steps = $trip->steps()->orderBy('order')->get();
        foreach ($steps as $i => $step) {
            $next = $steps->get($i + 1);

            // Dernière étape → champs à null si besoin
            if (!$next) {
                if ($step->distance_to_next !== null || $step->duration_to_next !== null) {
                    $step->update(['distance_to_next' => null, 'duration_to_next' => null]);
                }
                continue;
            }

            // Besoin de coordonnées valides
            if ($step->latitude && $step->longitude && $next->latitude && $next->longitude) {
                $profile = $this->mapTransportMode($step->transport_mode ?? null);

                $result = $this->mapbox->getDistanceAndDuration(
                    $step->latitude, $step->longitude,
                    $next->latitude, $next->longitude,
                    $profile
                );

                if ($result) {
                    $step->update([
                        'distance_to_next' => $result['distance'], // mètres
                        'duration_to_next' => $result['duration'], // secondes
                    ]);
                    continue;
                }
            }

            // Pas de coords ou erreur Mapbox → on vide
            $step->update(['distance_to_next' => null, 'duration_to_next' => null]);
        }
    }

    /** --------- Cache dates Trip --------- */
    public function refreshTripDates(Trip $trip): void
    {
        $min = $trip->steps()->min('start_date');
        $max = $trip->steps()->max('end_date');
        $trip->forceFill(['start_date' => $min, 'end_date' => $max])->save();
    }

    /**
     * Replanifie TOUTES les étapes du trip, de 1..N, en écrasant les dates.
     * - Si $tripStart est fourni : la 1re étape prend cette date.
     * - Sinon : on utilise start_date existante de la 1re étape (si absente → on s’arrête proprement).
     */
    public function rescheduleAllFromFirst(Trip $trip, ?Carbon $tripStart = null): void
    {
        DB::transaction(function () use ($trip, $tripStart) {
            $steps = $trip->steps()->orderBy('order')->get()->values();
            if ($steps->isEmpty()) {
                $this->refreshTripDates($trip);
                return;
            }

            $first = $steps[0];

            if ($tripStart) {
                $first->start_date = $tripStart->copy();
            } elseif (!$first->start_date) {
                // Pas d’ancre → on ne peut pas planifier
                $first->end_date = null;
                $first->save();
                $this->refreshTripDates($trip);
                return;
            }

            // 1) première étape
            $first->end_date = (!is_null($first->nights))
                ? $first->start_date->copy()->addDays((int)$first->nights)
                : null;
            $first->save();

            $prevEnd = $first->end_date;

            // 2) cascade autoritaire
            for ($i = 1; $i < $steps->count(); $i++) {
                $cur = $steps[$i];

                if (!$prevEnd) {
                    $cur->start_date = null;
                    $cur->end_date   = null;
                    $cur->save();
                    break;
                }

                $cur->start_date = $prevEnd->copy();
                if (!is_null($cur->nights)) {
                    $cur->end_date = $cur->start_date->copy()->addDays((int)$cur->nights);
                    $prevEnd = $cur->end_date->copy();
                } else {
                    $cur->end_date = null;
                    $prevEnd = null; // on ne peut plus cascader
                }

                $cur->save();
            }

            $this->refreshTripDates($trip);
            // Garder distances/durations à jour après rebuild global
            $this->recalcDistances($trip);
        });
    }

    /**
     * Après modif d’une étape (start_date ou nights), recalcule cette étape
     * puis TOUTES les suivantes en écrasant leurs dates. Met aussi à jour
     * le cache Trip + distances.
     */
    public function rescheduleFrom(Step $anchor): void
    {
        DB::transaction(function () use ($anchor) {
            $trip  = $anchor->trip()->with(['steps' => fn($q) => $q->orderBy('order')])->firstOrFail();
            $steps = $trip->steps;

            // 1) ancre
            if (!is_null($anchor->start_date) && !is_null($anchor->nights)) {
                $anchor->end_date = $anchor->start_date->copy()->addDays((int)$anchor->nights);
            } else {
                $anchor->end_date = null;
            }
            $anchor->save();

            // 2) cascade
            $idx = $steps->search(fn ($s) => $s->id === $anchor->id);
            $prevEnd = $anchor->end_date;

            for ($i = $idx + 1; $i < $steps->count(); $i++) {
                $cur = $steps[$i];

                if (!$prevEnd) {
                    $cur->start_date = null;
                    $cur->end_date   = null;
                    $cur->save();
                    break;
                }

                $cur->start_date = $prevEnd->copy();
                if (!is_null($cur->nights)) {
                    $cur->end_date = $cur->start_date->copy()->addDays((int)$cur->nights);
                    $prevEnd = $cur->end_date->copy();
                } else {
                    $cur->end_date = null;
                    $prevEnd = null;
                }

                $cur->save();
            }

            // 3) cache + distances
            $this->refreshTripDates($trip);
            $this->recalcDistances($trip);
        });
    }
}
