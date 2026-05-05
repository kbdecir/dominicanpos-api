<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(
        Request $request,
        int $companyId,
        int $branchId
    ): JsonResponse {
        $movements = StockMovement::query()
            ->with(['product', 'warehouse'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->when($request->filled('warehouse_id'), function ($query) use ($request) {
                $query->where('warehouse_id', $request->integer('warehouse_id'));
            })
            ->when($request->filled('product_id'), function ($query) use ($request) {
                $query->where('product_id', $request->integer('product_id'));
            })
            ->when($request->filled('movement_type'), function ($query) use ($request) {
                $query->where('movement_type', strtoupper($request->input('movement_type')));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            })
            ->orderByDesc('moved_at')
            ->orderByDesc('stock_movement_id')
            ->get();

        return response()->json([
            'message' => 'Movimientos de inventario obtenidos correctamente.',
            'data' => StockMovementResource::collection($movements),
        ]);
    }
}
