<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MapboxService
{
    protected string $baseUrl = 'https://api.mapbox.com/directions/v5/mapbox';

    public function getDistanceAndDuration(
        float $fromLat, float $fromLng,
        float $toLat, float $toLng,
        string $mode = 'driving' // driving, walking, cycling
    ): ?array {
        $response = Http::get("{$this->baseUrl}/{$mode}/{$fromLng},{$fromLat};{$toLng},{$toLat}", [
            'access_token' => config('services.mapbox.key'),
            'geometries' => 'geojson',
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if (!isset($data['routes'][0])) {
            return null;
        }

        return [
            'distance' => $data['routes'][0]['distance'] / 1000, // km
            'duration' => (int) round($data['routes'][0]['duration'] / 60), // minutes
        ];
    }
}
