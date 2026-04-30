<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CashMovementResource;
use App\Http\Resources\CashShiftResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloseCashShiftRequest;
use App\Http\Requests\OpenCashShiftRequest;
use App\Http\Requests\StoreCashMovementRequest;
use App\Services\CashMovementService;
use App\Services\CashShiftService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashShiftController extends Controller
{
    public function __construct(
        private readonly CashShiftService $cashShiftService,
        private readonly CashMovementService $cashMovementService,
    ) {}

    public function open(
        OpenCashShiftRequest $request,
        int $companyId,
        int $branchId
    ): JsonResponse {
        $user = $request->user();

        $shift = $this->cashShiftService->open([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'cash_register_id' => $request->integer('cash_register_id'),
            'cashier_user_id' => $user->user_id,
            'opened_by_user_id' => $user->user_id,
            'opening_amount' => $request->input('opening_amount'),
            'opening_notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => 'Turno abierto correctamente.',
            'data' => CashShiftResource::make(
                $shift->load(['cashRegister', 'cashier', 'openedBy'])
            ),
        ], 201);
    }

    public function current(
        Request $request,
        int $companyId,
        int $branchId
    ): JsonResponse {
        $shift = $this->cashShiftService->currentOpenShift(
            companyId: $companyId,
            branchId: $branchId,
            userId: $request->user()->user_id
        );

        return response()->json([
            'message' => 'Turno abierto obtenido correctamente.',
            'data' => $shift ? CashShiftResource::make($shift) : null,
        ]);
    }

    public function close(
        CloseCashShiftRequest $request,
        int $companyId,
        int $branchId,
        int $cashShiftId
    ): JsonResponse {
        $shift = $this->cashShiftService->findOpenForBranch(
            cashShiftId: $cashShiftId,
            companyId: $companyId,
            branchId: $branchId
        );

        $shift = $this->cashShiftService->close($shift, [
            'counted_cash_amount' => $request->input('counted_amount'),
            'closed_by_user_id' => $request->user()->user_id,
            'closing_notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => 'Turno cerrado correctamente.',
            'data' => CashShiftResource::make($shift),
        ]);
    }

    public function storeMovement(
        StoreCashMovementRequest $request,
        int $companyId,
        int $branchId,
        int $cashShiftId
    ): JsonResponse {
        $shift = $this->cashShiftService->findForBranch(
            cashShiftId: $cashShiftId,
            companyId: $companyId,
            branchId: $branchId
        );

        $movement = $this->cashMovementService->create($shift, [
            'user_id' => $request->user()->user_id,
            'created_by_user_id' => $request->user()->user_id,
            'type' => $request->input('type'),
            'amount' => $request->input('amount'),
            'reason' => $request->input('reason'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => 'Movimiento registrado correctamente.',
            'data' => CashMovementResource::make($movement),
        ], 201);
    }
}
