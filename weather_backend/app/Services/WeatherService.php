<?php

namespace App\Services;

use App\Models\City;
use App\Models\Source;
use App\Models\WeatherRecord;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class WeatherService {
  private const TTL_HOURS = 3;

  public function getDaily(City $city, Source $source, Carbon $date): WeatherRecord {
    // Ключ, по которому будет осуществляться поиск в базе и для сохранения тож нужен
    $key = [
      'city_id' => $city->id,
      'source_id' => $source->id,
      'date' => $date->toDateString(),
      'kind' => $date->lt(today()) ? 'actual' : 'forecast',
    ];

    // Проверка существования и свежести записи в базе
    $record = WeatherRecord::where($key)->first();
    if ($record && $this->isFresh($record)) {
      return $record;
    }

    $daily = $this->fetchForecast($city, $date);

    // Сохранение или обновление записи в базе
    return WeatherRecord::updateOrCreate($key, [
      'temp_max' => $daily['temperature_2m_max'][0],
      'temp_min' => $daily['temperature_2m_min'][0],
      'precipitation' => $daily['precipitation_sum'][0],
      'wind_speed' => $daily['wind_speed_10m_max'][0],
      'fetched_at' => now(),
    ]);
  }

  private function isFresh(WeatherRecord $record): bool {
    if ($record->date->lt(today())) {
      return true; // Для прошлых дат не проверяем свежесть
    }
    return $record->fetched_at->gt(now()->subHours(self::TTL_HOURS));
  }
  
  private function fetchForecast(City $city, Carbon $date): array {
    try {
      $response = Http::get('https://api.open-meteo.com/v1/forecast', [
        'latitude' => $city->lat,
        'longitude' => $city->lon,
        'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max',
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

    return $response->json('daily');
  }
}