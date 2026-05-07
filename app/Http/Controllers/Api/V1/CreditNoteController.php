<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreditNoteRequest;
use App\Http\Resources\CreditNoteResource;
use App\Services\CreditNoteService;
use Illuminate\Http\JsonResponse;

class CreditNoteController extends Controller
{
    public function __construct(
        private readonly CreditNoteService $creditNoteService,
    ) {}

    public function store(
        StoreCreditNoteRequest $request,
        int $companyId,
        int $branchId
    ): JsonResponse {
        $creditNote = $this->creditNoteService->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'original_sale_id' => $request->integer('original_sale_id'),
            'fiscal_document_type_id' => $request->integer('fiscal_document_type_id'),
            'reason' => $request->input('reason'),
            'items' => $request->input('items'),
            'created_by_user_id' => $request->user()->user_id,
        ]);

        return response()->json([
            'message' => 'Nota de crédito emitida correctamente.',
            'data' => CreditNoteResource::make($creditNote),
        ], 201);
    }
}
