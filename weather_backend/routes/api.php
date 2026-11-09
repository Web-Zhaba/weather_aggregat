<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Services\CityResolver;

Route::prefix('v1')->group(function () {
    Route::get('/weather', function (Request $request, CityResolver $resolver) {
        // return weather data;
        $validated = $request->validate([
            'latitude' => 'required_without:city|numeric|between:-90,90',
            'longitude' => 'required_without:city|numeric|between:-180,180',
            'date' => 'nullable|date',
            'city' => 'required_without:latitude|string|min:2|max:100',
            'country' => 'nullable|string|min:2|max:100',
        ]);

        $city = null;
        if (isset($validated['city'])) {
            try {
                $result = $resolver->resolve($validated['city'], $validated['country'] ?? null);
            } catch (\RuntimeException $e) {
                return response()->json(['error' => 'Geocoding service is unavailable. Please try again later.'], 502);
            }
            if (!$result) {
                return response()->json(['error' => 'City not found.'], 404);
            }
            $city = $result['city'];
            $validated['latitude'] = $city->lat;
            $validated['longitude'] = $city->lon;
        }

        try {
            $response = Http::get(
                "https://api.open-meteo.com/v1/forecast",
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
                    'timezone' => 'auto',
                    'start_date' => $validated['date'] ?? now()->toDateString(),
                    'end_date' => $validated['date'] ?? now()->toDateString(),
                ]
            );
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Weather service is unavailable. Please try again later.'], 502);
        }
        if ($response->failed()) {
            return response()->json(['error' => 'Weather service is unavailable. Please try again later.'], 502);
        }
        $unformatted_data = $response->json();
        $formatted_data = [
            'timezone' => $unformatted_data['timezone'],
            'daily' => [],
        ];
        foreach ($unformatted_data['daily'] as $key => $value) {
            $formatted_data['daily'][$key] = $value;
        }
        if ($city) {
            $formatted_data['city'] = $city;
        }
        return $formatted_data;
    });

    // Поиск города по названию и сохранение его в базу данных, если он не найден (геокодинг короче)
    Route::get('/cities/resolve', function (Request $request, CityResolver $resolver) {
        $validated = $request->validate([
            'city' => 'required|string|min:2|max:100',
            'country' => 'nullable|string|min:2|max:100',
        ]);

        try {
            $result = $resolver->resolve($validated['city'], $validated['country'] ?? null);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => 'Geocoding service is unavailable. Please try again later.'], 502);
        }

        if (!$result) {
            return response()->json(['error' => 'City not found.'], 404);
        }

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