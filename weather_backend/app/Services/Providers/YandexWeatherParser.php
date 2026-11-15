<?php

namespace App\Services\Providers;

use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class YandexWeatherParser {
  private const MONTHS = [
    'января' => 1,
    'февраля' => 2,
    'марта' => 3,
    'апреля' => 4,
    'мая' => 5,
    'июня' => 6,
    'июля' => 7,
    'августа' => 8,
    'сентября' => 9,
    'октября' => 10,
    'ноября' => 11,
    'декабря' => 12,
  ];
  
  public function parse(string $html): array {
    $crawler = new Crawler($html);

    $days = $crawler->filter('article[class*="AppForecastDay_container"]');
    $parsed = $days->each(function (Crawler $node){
      $unformattedDate = $node->filter('h3[class*="AppForecastDayHeader_dayTitle"]')->text();
      $unformattedDate = str_replace("\u{00A0}", ' ', $unformattedDate);
      preg_match('/(\d+)\s+(\p{L}+)/u', $unformattedDate, $matches); 
      $month = self::MONTHS[$matches[2]];
      $temps = $node->filter('div[class*="AppForecastDayPart_temp__"]')->each(fn (Crawler $t) => (float) str_replace(['−', '+', '°'], ['-', '', ''], $t->text()));
      $tempMax = max($temps);
      $tempMin = min($temps);
      $winds = $node->filter('div[class*="AppForecastDayPart_wind__"]')->each(fn (Crawler $w) => (float) $w->text());
      $windSpeed = max($winds);
      $humidities = $node->filter('div[style*="-hum"]')->each(fn (Crawler $h) => (float) $h->text());
      $humidity = $humidities ? round(array_sum($humidities) / count($humidities), 1) : null;

      return [
        'day' => (int) $matches[1],
        'month' => $month,
        'temp_max' => $tempMax,
        'temp_min' => $tempMin,
        'precipitation' => null,
        'wind_speed' => $windSpeed,
        'humidity' => $humidity,
      ];
    }); 

    $unique = [];
    foreach ($parsed as $d) {
      $unique[$d['month'] . '-' . $d['day']] = $d;
    }
    
    $year = now()->year;
    $prevMonth = null;
    $result = [];
    foreach ($unique as $d) {
      if ($prevMonth !== null && $d['month'] < $prevMonth) {
        $year++;
      }
      $prevMonth = $d['month'];
      $date = Carbon::create($year, $d['month'], $d['day'])->toDateString();
      $result[$date] = [
        'temp_max' => $d['temp_max'],
        'temp_min' => $d['temp_min'],
        'precipitation' => null,
        'wind_speed' => $d['wind_speed'],
        'humidity' => $d['humidity'],
      ];
    }
    
    return $result;
  }
}
