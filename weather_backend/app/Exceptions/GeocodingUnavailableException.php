<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class GeocodingUnavailableException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['error' => 'Geocoding service is unavailable. Please try again later.'], 502);
    }
}
