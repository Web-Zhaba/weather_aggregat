<?php

namespace App\Services;

use App\Exceptions\CityNotFoundException;
use App\Exceptions\GeocodingUnavailableException;
use App\Models\City;
use App\Models\CityAlias;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Единая точка определения города для всех провайдеров погоды.
 * Провайдеры получают готовый City (координаты, timezone) и сами ничего не геокодируют.
 */
class CityResolver
{
    private const GEOCODING_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    /**
     * @return array{city: City, source: 'database'|'geocoding'}
     * @throws CityNotFoundException
     * @throws GeocodingUnavailableException
     */
    public function resolve(string $name, ?string $country = null): array
    {
        $query = self::normalize($name);
        $countryKey = self::normalize($country);

        $alias = CityAlias::with('city')
            ->where(['query' => $query, 'country' => $countryKey])
            ->first();
        if ($alias) {
            return ['city' => $alias->city, 'source' => 'database'];
        }

        $place = $this->geocode($name, $country);
        if (!$place) {
            throw new CityNotFoundException();
        }

        $city = $this->storeCity($place);
        CityAlias::firstOrCreate(
            ['query' => $query, 'country' => $countryKey],
            ['city_id' => $city->id],
        );

        return ['city' => $city, 'source' => 'geocoding'];
    }

    // Приводит пользовательский ввод к виду, по которому ищем алиас.
    public static function normalize(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = str_replace('ё', 'е', $value);

        return preg_replace('/\s+/u', ' ', $value);
    }

    private function geocode(string $name, ?string $country): ?array
    {
        try {
            $response = Http::get(self::GEOCODING_URL, [
                'name' => $name,
                'count' => 10,
                // Open-Meteo находит название только на языке запроса, поэтому кириллицу ищем на русском
                'language' => preg_match('/\p{Cyrillic}/u', $name) ? 'ru' : 'en',
                'format' => 'json',
            ]);
        } catch (ConnectionException $e) {
            throw new GeocodingUnavailableException('Geocoding service is unavailable.', 0, $e);
        }

        if (!$response->successful()) {
            throw new GeocodingUnavailableException('Geocoding service is unavailable.');
        }

        $results = $response->json('results', []);
        if ($country !== null) {
            // Open-Meteo не фильтрует по стране, поэтому фильтруем здесь (код ISO или название)
            $results = array_values(array_filter($results, fn ($r) =>
                strcasecmp($r['country_code'] ?? '', $country) === 0
                || mb_strtolower($r['country'] ?? '') === mb_strtolower($country)
            ));
        }

        return $results[0] ?? null;
    }

    /** Один город = один geonames_id, на каком бы языке его ни нашли. */
    private function storeCity(array $place): City
    {
        $attributes = [
            'name' => $place['name'],
            'country' => $place['country'] ?? '',
            'country_code' => $place['country_code'] ?? null,
            'lat' => $place['latitude'],
            'lon' => $place['longitude'],
            'timezone' => $place['timezone'] ?? null,
        ];

        return City::firstOrCreate(['geonames_id' => $place['id']], $attributes);
    }
}
