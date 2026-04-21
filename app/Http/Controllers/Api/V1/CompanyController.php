<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $companies = $this->companyService->listForUser($request->user());

        return response()->json([
            'message' => 'Empresas obtenidas correctamente.',
            'data' => $companies,
        ]);
    }

    public function store(CompanyStoreRequest $request): JsonResponse
    {
        $company = $this->companyService->create($request->validated(), $request->user());

        return response()->json([
            'message' => 'Empresa creada correctamente.',
            'data' => $company,
        ], 201);
    }

    public function show(int $companyId, Request $request): JsonResponse
    {
        $company = $this->companyService->findForUser($companyId, $request->user());

        return response()->json([
            'message' => 'Empresa obtenida correctamente.',
            'data' => $company,
        ]);
    }

    public function update(CompanyUpdateRequest $request, int $companyId): JsonResponse
    {
        $company = $this->companyService->findForUser($companyId, $request->user());
        $company = $this->companyService->update($company, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Empresa actualizada correctamente.',
            'data' => $company,
        ]);
    }

    public function activate(Request $request, int $companyId): JsonResponse
    {
        $company = $this->companyService->findForUser($companyId, $request->user());
        $company = $this->companyService->toggleStatus($company, true, $request->user());

        return response()->json([
            'message' => 'Empresa activada correctamente.',
            'data' => $company,
        ]);
    }

    public function deactivate(Request $request, int $companyId): JsonResponse
    {
        $company = $this->companyService->findForUser($companyId, $request->user());
        $company = $this->companyService->toggleStatus($company, false, $request->user());

        return response()->json([
            'message' => 'Empresa inactivada correctamente.',
            'data' => $company,
        ]);
    }
}
