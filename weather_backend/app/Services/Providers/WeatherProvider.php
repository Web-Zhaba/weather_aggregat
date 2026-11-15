<?php

namespace App\Services\Providers;

use App\Models\City;
use Illuminate\Support\Carbon;

interface WeatherProvider
{
    /** @return array{temp_min: ?float, temp_max: ?float, precipitation: ?float, wind_speed: ?float} */
    public function fetchDaily(City $city, Carbon $date): array;
}