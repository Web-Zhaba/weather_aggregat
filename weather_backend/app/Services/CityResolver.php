<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CityResolver
{
    /**
     * Returns ['city' => City, 'source' => 'database'|'geocoding'],
     * null if the city is not found, throws if the geocoding service fails.
     */
    public function resolve(string $name, ?string $country = null): ?array
    {
        $query = City::where('name', $name);
        if ($country !== null) {
            $query->where('country', $country);
        }
        $city = $query->first();

        if ($city) {
            return ['city' => $city, 'source' => 'database'];
        }

        $response = Http::get(
            "https://geocoding-api.open-meteo.com/v1/search",
            [
                'name' => $name,
                'count' => 10,
                // Open-Meteo находит название только на языке запроса, поэтому кириллицу ищем на русском
                'language' => preg_match('/\p{Cyrillic}/u', $name) ? 'ru' : 'en',
                'format' => 'json',
            ]
        );

        if (!$response->successful()) {
            throw new RuntimeException('Geocoding service is unavailable.');
        }

        $results = $response->json('results', []);
        if ($country !== null) {
            // Open-Meteo не фильтрует по стране, поэтому фильтруем здесь (код ISO или название)
            $results = array_values(array_filter($results, fn ($r) =>
                strcasecmp($r['country_code'] ?? '', $country) === 0
                || strcasecmp($r['country'] ?? '', $country) === 0
            ));
        }
        $place = $results[0] ?? null;

        if (!$place) {
            return null;
        }

        $city = City::firstOrCreate(
            [
                'name' => $place['name'],
                'country' => $place['country'],
            ],
            [
                'lat' => $place['latitude'],
                'lon' => $place['longitude'],
            ]
        );

        return ['city' => $city, 'source' => 'geocoding'];
    }
}
