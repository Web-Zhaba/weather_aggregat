<?php

namespace App\Services\Providers;

use App\Models\City;
use RuntimeException;
use Illuminate\Support\Carbon;

class YandexProvider implements WeatherProvider {
  private const FIXTURE = 'tests/fixtures/yandex.cherepovets.html';

  public function fetchDaily(City $city, Carbon $date): array {
    $days = (new YandexWeatherParser())->parse($this->fetchHtml($city));
    $day = $days[$date->toDateString()] ?? null;
    if (!$day) {
      throw new RuntimeException("Weather data for {$date->toDateString()} not found in Yandex forecast.");
    }
    return $day;
  }

  /**
   * Для live-режима достаточно заменить тело на запрос по координатам города:
   *   Http::get('https://yandex.ru/pogoda/', ['lat' => $city->lat, 'lon' => $city->lon])
   * с той же обработкой ошибок через RuntimeException парсер и остальной код не меняются.
   */
  private function fetchHtml(City $city): string {
    $path = base_path(self::FIXTURE);
    if (!is_file($path)) {
      throw new RuntimeException('Yandex fixture not found.');
    }
    return file_get_contents($path);
  }
}
