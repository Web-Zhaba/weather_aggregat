<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::prefix('v1')->group(function () {
    Route::get('/weather', function (Request $request) {
        // return weather data;
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        $response = Http::get(
            "https://api.open-meteo.com/v1/forecast",
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum',
                'timezone' => 'auto',
                'forecast_days' => 1,
            ]
        );
        $unformatted_data = $response->json();
        $formatted_data = [
            'timezone' => $unformatted_data['timezone'],
            'daily' => [],
        ];
        foreach ($unformatted_data['daily'] as $key => $value) {
            $formatted_data['daily'][$key] = $value;
        }
        return $formatted_data;
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