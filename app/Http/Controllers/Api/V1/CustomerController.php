<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService,
    ) {}

    public function index(Request $request, int $companyId): JsonResponse
    {
        $customers = $this->customerService->list($companyId, [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'customer_type' => $request->input('customer_type'),
            'per_page' => $request->integer('per_page', 20),
        ]);

        return response()->json([
            'message' => 'Clientes obtenidos correctamente.',
            'data' => CustomerResource::collection($customers),
        ]);
    }

    public function store(StoreCustomerRequest $request, int $companyId): JsonResponse
    {
        $customer = $this->customerService->create(
            $companyId,
            $request->validated()
        );

        return response()->json([
            'message' => 'Cliente creado correctamente.',
            'data' => CustomerResource::make($customer),
        ], 201);
    }

    public function show(int $companyId, int $customerId): JsonResponse
    {
        $customer = $this->customerService->findForCompany($companyId, $customerId);

        return response()->json([
            'message' => 'Cliente obtenido correctamente.',
            'data' => CustomerResource::make($customer),
        ]);
    }

    public function update(
        UpdateCustomerRequest $request,
        int $companyId,
        int $customerId
    ): JsonResponse {
        $customer = $this->customerService->findForCompany($companyId, $customerId);

        $customer = $this->customerService->update(
            $customer,
            $request->validated()
        );

        return response()->json([
            'message' => 'Cliente actualizado correctamente.',
            'data' => CustomerResource::make($customer),
        ]);
    }

    public function findByTaxId(Request $request, int $companyId): JsonResponse
    {
        $request->validate([
            'tax_id' => ['required', 'string', 'max:30'],
        ]);

        $customer = $this->customerService->findByTaxId(
            $companyId,
            $request->input('tax_id')
        );

        return response()->json([
            'message' => $customer
                ? 'Cliente encontrado correctamente.'
                : 'Cliente no encontrado.',
            'data' => $customer ? CustomerResource::make($customer) : null,
        ]);
    }
}
