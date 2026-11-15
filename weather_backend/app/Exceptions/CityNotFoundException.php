<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class CityNotFoundException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['error' => 'City not found.'], 404);
    }
}
