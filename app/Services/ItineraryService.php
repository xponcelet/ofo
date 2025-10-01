<?php

namespace App\Services;

use App\Models\Trip;

class ItineraryService
{
    public function __construct(private MapboxService $mapbox) {}

    /**
     * Mappe nos modes UI → profils Mapbox.
     * (pour l’instant, train/plane/boat/bus fallback sur 'driving')
     */
    private function mapTransportMode(?string $mode): string
    {
        return match ($mode) {
            'car'  => 'driving',
            'bike' => 'cycling',
            'walk' => 'walking',
            // TODO: train/plane/boat/bus → logique spécifique si besoin
            default => 'driving',
        };
    }

    /**
     * Met à jour distance_to_next / duration_to_next pour toutes les étapes d’un trip.
     */
    public function recalcDistances(Trip $trip): void
    {
        $steps = $trip->steps()->orderBy('order')->get();

        foreach ($steps as $i => $step) {
            $next = $steps->get($i + 1);

            // Dernière étape → pas de trajet suivant
            if (!$next) {
                if ($step->distance_to_next !== null || $step->duration_to_next !== null) {
                    $step->update([
                        'distance_to_next' => null,
                        'duration_to_next' => null,
                    ]);
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
                        'distance_to_next' => $result['distance'], // en mètres
                        'duration_to_next' => $result['duration'], // en secondes
                    ]);
                    continue;
                }
            }

            // Si on arrive ici: pas de coords ou erreur Mapbox → on vide
            $step->update([
                'distance_to_next' => null,
                'duration_to_next' => null,
            ]);
        }
    }
}
