<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyUserInviteRequest;
use App\Http\Requests\CompanyUserUpdateRoleRequest;
use App\Services\CompanyUserService;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyUserController extends Controller
{
    public function __construct(
    private readonly CompanyUserService $companyUserService,
    private readonly InvitationService $invitationService,
    ) {
    }

    public function index(int $companyId, Request $request): JsonResponse
    {
        $users = $this->companyUserService->listByCompany($companyId, $request->user());

        return response()->json([
            'message' => 'Usuarios de la empresa obtenidos correctamente.',
            'data' => $users,
        ]);
    }

    public function updateRole(
        CompanyUserUpdateRoleRequest $request,
        int $companyId,
        int $userId
    ): JsonResponse {
        $access = $this->companyUserService->changeRole(
            $companyId,
            $userId,
            $request->integer('role_id'),
            $request->user()
        );

        return response()->json([
            'message' => 'Rol del usuario actualizado correctamente.',
            'data' => $access,
        ]);
    }

    public function activate(int $companyId, int $userId, Request $request): JsonResponse
    {
        $access = $this->companyUserService->activate($companyId, $userId, $request->user());

        return response()->json([
            'message' => 'Acceso del usuario activado correctamente.',
            'data' => $access,
        ]);
    }

    public function deactivate(int $companyId, int $userId, Request $request): JsonResponse
    {
        $access = $this->companyUserService->deactivate($companyId, $userId, $request->user());

        return response()->json([
            'message' => 'Acceso del usuario inactivado correctamente.',
            'data' => $access,
        ]);
    }

    public function permissions(int $companyId, int $userId, Request $request): JsonResponse
    {
        $permissions = $this->companyUserService->permissions($companyId, $userId, $request->user());

        return response()->json([
            'message' => 'Permisos del usuario obtenidos correctamente.',
            'data' => $permissions,
        ]);
    }

    public function invite(
    CompanyUserInviteRequest $request,
    int $companyId
): JsonResponse {
    return response()->json([
        'ok' => true,
        'company_id' => $companyId,
        'payload' => $request->validated(),
        'user_id' => $request->user()?->user_id,
    ]);
}

}
