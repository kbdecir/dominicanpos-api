<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CashRegisterResource;
use App\Http\Controllers\Controller;
use App\Services\CashRegisterService;
use Illuminate\Http\JsonResponse;

class CashRegisterController extends Controller
{
    public function __construct(
        private readonly CashRegisterService $cashRegisterService,
    ) {}

    public function active(
        int $companyId,
        int $branchId
    ): JsonResponse {
        $registers = $this->cashRegisterService->getActiveRegisters(
            companyId: $companyId,
            branchId: $branchId
        );

        return response()->json([
            'message' => 'Cajas activas obtenidas correctamente.',
            'data' => CashRegisterResource::collection($registers),
        ]);
    }
}
