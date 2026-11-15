<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Services\CityResolver;
use App\Services\WeatherService;
use App\Models\Source;
use Illuminate\Support\Carbon;

Route::prefix('v1')->group(function () {
    Route::get('/weather', function (Request $request, CityResolver $resolver, WeatherService $weather) {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'city' => 'required|string|min:2|max:100',
            'country' => 'nullable|string|min:2|max:100',
        ]);

        // 404 / 502 отдают сами исключения CityResolver
        $city = $resolver->resolve($validated['city'], $validated['country'] ?? null)['city'];

        // Если город найден в базе данных, используем его для получения прогноза погоды через WeatherService
        $date = isset($validated['date']) ? Carbon::parse($validated['date']) : today();
        $sources = [];

        foreach (Source::where('is_active', true)->get() as $s) {
            try {
                $record = $weather->getDaily($city, $s, $date);
                $sources[] = [
                    'source' => $s->slug,
                    'name' => $s->name,
                    'city' => $city,
                    'date' => $record->date->toDateString(),
                    'temp_max' => $record->temp_max,
                    'temp_min' => $record->temp_min,
                    'precipitation' => $record->precipitation,
                    'wind_speed' => $record->wind_speed,
                    'humidity' => $record->humidity,
                ];
            } catch (\RuntimeException $e) {
                $sources[] = [
                    'source' => $s->slug,
                    'name' => $s->name,
                    'error' => 'unavailable'
                ];
            }
        }
        return ['city' => $city, 'date' => $date->toDateString(), 'sources' => $sources];
    });

    // Поиск города по названию и сохранение его в базу данных, если он не найден (геокодинг короче)
    Route::get('/cities/resolve', function (Request $request, CityResolver $resolver) {
        $validated = $request->validate([
            'city' => 'required|string|min:2|max:100',
            'country' => 'nullable|string|min:2|max:100',
        ]);

        $result = $resolver->resolve($validated['city'], $validated['country'] ?? null);

        return response()->json(
            ['city' => $result['city'], 'source' => $result['source']],
            $result['source'] === 'geocoding' ? 201 : 200
        );
    });

    Route::post('/register', function() {
        // return register response;
    });
    Route::post('/login', function() {
        // return login response;
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/favorites', function (Request $request) {
            // return $request->user()->favorites;
        });
        Route::get('/user', function (Request $request) {
            // return $request->user();
        });
    });
});