<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryBalanceResource;
use App\Models\InventoryBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryBalanceController extends Controller
{
    public function index(
        Request $request,
        int $companyId,
        int $branchId
    ): JsonResponse {
        $balances = InventoryBalance::query()
            ->with(['product', 'warehouse'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->when($request->filled('warehouse_id'), function ($query) use ($request) {
                $query->where('warehouse_id', $request->integer('warehouse_id'));
            })
            ->when($request->filled('product_id'), function ($query) use ($request) {
                $query->where('product_id', $request->integer('product_id'));
            })
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->get();

        return response()->json([
            'message' => 'Balances de inventario obtenidos correctamente.',
            'data' => InventoryBalanceResource::collection($balances),
        ]);
    }
}
