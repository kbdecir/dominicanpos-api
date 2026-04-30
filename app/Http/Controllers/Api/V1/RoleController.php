<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleSyncPermissionsRequest;
use App\Services\RoleService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');

        $roles = $this->roleService->list(
            $companyId ? (int) $companyId : null
        );

        return response()->json([
            'message' => 'Roles obtenidos correctamente.',
            'data' => $roles,
        ]);
    }

    public function permissions(int $roleId): JsonResponse
    {
        $permissions = $this->roleService->permissions($roleId);

        return response()->json([
            'message' => 'Permisos del rol obtenidos correctamente.',
            'data' => $permissions,
        ]);
    }

    public function syncPermissions(
        RoleSyncPermissionsRequest $request,
        int $roleId
    ): JsonResponse {
        $role = $this->roleService->syncPermissions(
            $roleId,
            $request->validated('permission_ids')
        );

        return response()->json([
            'message' => 'Permisos del rol actualizados correctamente.',
            'data' => $role,
        ]);
    }

    public function store(
        RoleStoreRequest $request,
        int $companyId
    ): JsonResponse {
        $role = $this->roleService->createCompanyRole(
            $companyId,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Rol creado correctamente.',
            'data' => $role,
        ], 201);
    }

    public function update(
        RoleUpdateRequest $request,
        int $companyId,
        int $roleId
    ): JsonResponse {
        $role = $this->roleService->updateCompanyRole(
            $companyId,
            $roleId,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Rol actualizado correctamente.',
            'data' => $role,
        ]);
    }
}
