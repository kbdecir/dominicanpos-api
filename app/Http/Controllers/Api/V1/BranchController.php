<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchStoreRequest;
use App\Http\Requests\BranchUpdateRequest;
use App\Services\BranchService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchService $branchService,
        private readonly CompanyService $companyService,
    ) {
    }

    public function indexByCompany(int $companyId, Request $request): JsonResponse
    {
        $company = $this->companyService->findForUser($companyId, $request->user());
        $branches = $this->branchService->listByCompany($company);

        return response()->json([
            'message' => 'Sucursales obtenidas correctamente.',
            'data' => $branches,
        ]);
    }

    public function store(BranchStoreRequest $request): JsonResponse
    {
        $company = $this->companyService->findForUser($request->integer('company_id'), $request->user());
        $branch = $this->branchService->create($request->validated(), $company, $request->user());

        return response()->json([
            'message' => 'Sucursal creada correctamente.',
            'data' => $branch,
        ], 201);
    }

    public function show(int $branchId, Request $request): JsonResponse
    {
        $branch = $this->branchService->findForUser($branchId, $request->user());

        return response()->json([
            'message' => 'Sucursal obtenida correctamente.',
            'data' => $branch,
        ]);
    }

    public function update(BranchUpdateRequest $request, int $branchId): JsonResponse
    {
        $branch = $this->branchService->findForUser($branchId, $request->user());
        $branch = $this->branchService->update($branch, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Sucursal actualizada correctamente.',
            'data' => $branch,
        ]);
    }

    public function activate(Request $request, int $branchId): JsonResponse
    {
        $branch = $this->branchService->findForUser($branchId, $request->user());
        $branch = $this->branchService->toggleStatus($branch, true, $request->user());

        return response()->json([
            'message' => 'Sucursal activada correctamente.',
            'data' => $branch,
        ]);
    }

    public function deactivate(Request $request, int $branchId): JsonResponse
    {
        $branch = $this->branchService->findForUser($branchId, $request->user());
        $branch = $this->branchService->toggleStatus($branch, false, $request->user());

        return response()->json([
            'message' => 'Sucursal inactivada correctamente.',
            'data' => $branch,
        ]);
    }
}
