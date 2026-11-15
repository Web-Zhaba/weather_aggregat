<?php

namespace App\Services;

use App\Models\City;
use App\Models\Source;
use App\Models\WeatherRecord;
use App\Services\Providers\WeatherProvider;
use App\Services\Providers\OpenMeteoProvider;
use App\Services\Providers\YandexProvider;
use RuntimeException;
use Illuminate\Support\Carbon;

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

    $data = $this->provider($source)->fetchDaily($city, $date);

    // Сохранение или обновление записи в базе
    return WeatherRecord::updateOrCreate($key, [
      'temp_max' => $data['temp_max'],
      'temp_min' => $data['temp_min'],
      'precipitation' => $data['precipitation'],
      'wind_speed' => $data['wind_speed'],
      'humidity' => $data['humidity'],
      'fetched_at' => now(),
    ]);
  }

  // Все провайдеры погоды
  private function provider(Source $source): WeatherProvider {
    return match ($source->slug) {
        'open-meteo' => new OpenMeteoProvider(),
        'yandex' => new YandexProvider(),
        default => throw new RuntimeException("Unknown source: {$source->slug}"),
    };
  }

  private function isFresh(WeatherRecord $record): bool {
    if ($record->date->lt(today())) {
      return true; // Для прошлых дат не проверяем свежесть
    }
    return $record->fetched_at->gt(now()->subHours(self::TTL_HOURS));
  }
}