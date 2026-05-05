<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService,
    ) {}

    public function store(
        StoreSaleRequest $request,
        int $companyId,
        int $branchId
    ): JsonResponse {
        $sale = $this->saleService->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'warehouse_id' => $request->integer('warehouse_id'),
            'cash_register_id' => $request->integer('cash_register_id'),
            'customer_id' => $request->input('customer_id'),
            'sold_by_user_id' => $request->user()->user_id,
            'sale_type' => $request->input('sale_type', 'TICKET'),
            'notes' => $request->input('notes'),
            'items' => $request->input('items'),
            'payments' => $request->input('payments'),
            'fiscal_document_type_id' => $request->input('fiscal_document_type_id'),
        ]);

        return response()->json([
            'message' => 'Venta registrada correctamente.',
            'data' => SaleResource::make($sale),
        ], 201);
    }
}
