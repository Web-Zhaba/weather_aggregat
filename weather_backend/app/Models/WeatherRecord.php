<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherRecord extends Model
{
    protected $fillable = ['city_id', 'source_id', 'date', 'kind', 'temp_min', 'temp_max', 'precipitation', 'wind_speed', 'humidity', 'fetched_at'];

    protected function casts(): array {
        return [
            'date' => 'date',
            'fetched_at' => 'datetime',
            'temp_min' => 'float',
            'temp_max' => 'float',
            'precipitation' => 'float',
            'wind_speed' => 'float',
            'humidity' => 'float',
        ];
    }
}
