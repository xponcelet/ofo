<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    /**
     * Récupère le code pays (ex: FR, BE...) à partir des coordonnées GPS.
     */
    public function getCountryCode(float $lat, float $lng): ?string
    {
        $apiKey = config('services.mapbox.key');

        if (!$apiKey) {
            \Log::warning('Mapbox key manquante dans config/services.php');
            return null;
        }

        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/{$lng},{$lat}.json";
        $response = Http::get($url, [
            'access_token' => $apiKey,
            'types' => 'country',
            'limit' => 1,
        ]);

        if ($response->successful() && isset($response['features'][0]['properties']['short_code'])) {
            return strtoupper($response['features'][0]['properties']['short_code']);
        }

        return null;
    }
}
