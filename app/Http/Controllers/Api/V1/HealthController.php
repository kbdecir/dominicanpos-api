<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API DominicanPOS operando correctamente',
            'data' => [
                'service' => 'dominicanpos-api',
                'version' => 'v1',
            ],
        ]);
    }
}
