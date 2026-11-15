<?php

namespace App\Services\Providers;

use App\Models\City;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class OpenMeteoProvider implements WeatherProvider {
  public function fetchDaily(City $city, Carbon $date): array {
    try {
      $response = Http::get('https://api.open-meteo.com/v1/forecast', [
        'latitude' => $city->lat,
        'longitude' => $city->lon,
        'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,relative_humidity_2m_mean',
        'wind_speed_unit' => 'ms',
        'timezone' => 'auto',
        'start_date' => $date->toDateString(),
        'end_date' => $date->toDateString(),
      ]);
    } catch (ConnectionException $e) {
      throw new RuntimeException('Weather service is unavailable.', 0, $e);
    }

    if ($response->failed()) {
      throw new RuntimeException('Weather service is unavailable.');
    }

    $daily = $response->json('daily');

    return [
      'temp_max' => $daily['temperature_2m_max'][0] ?? null,
      'temp_min' => $daily['temperature_2m_min'][0] ?? null,
      'precipitation' => $daily['precipitation_sum'][0] ?? null,
      'wind_speed' => $daily['wind_speed_10m_max'][0] ?? null,
      'humidity' => $daily['relative_humidity_2m_mean'][0] ?? null,
    ];
  }
}