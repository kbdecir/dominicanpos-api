<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserCompanyRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyUserService
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function listByCompany(int $companyId, User $actor): Collection
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        return UserCompanyRole::query()
            ->with([
                'user:user_id,first_name,last_name,email',
                'role:role_id,name,code',
                'branch:branch_id,name',
            ])
            ->where('company_id', $companyId)
            ->orderByDesc('is_default_company')
            ->orderBy('user_id')
            ->get();
    }

    public function changeRole(int $companyId, int $userId, int $roleId, User $actor): UserCompanyRole
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $access = UserCompanyRole::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->first();

        if (! $access) {
            throw new NotFoundHttpException('Acceso de usuario no encontrado para esta empresa.');
        }

        $role = Role::query()
            ->where('role_id', $roleId)
            ->first();

        if (! $role) {
            throw new NotFoundHttpException('Rol no encontrado.');
        }

        return DB::transaction(function () use ($access, $role) {
            $access->update([
                'role_id' => $role->role_id,
                'assigned_at' => now(),
            ]);

            return $access->fresh([
                'user:user_id,first_name,last_name,email',
                'role:role_id,name,code',
                'branch:branch_id,name',
            ]);
        });
    }

    public function activate(int $companyId, int $userId, User $actor): UserCompanyRole
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $access = UserCompanyRole::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->first();

        if (! $access) {
            throw new NotFoundHttpException('Acceso de usuario no encontrado para esta empresa.');
        }

        $access->update([
            'status' => 'ACTIVE',
            'revoked_at' => null,
        ]);

        return $access->fresh([
            'user:user_id,first_name,last_name,email',
            'role:role_id,name,code',
            'branch:branch_id,name',
        ]);
    }

    public function deactivate(int $companyId, int $userId, User $actor): UserCompanyRole
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $access = UserCompanyRole::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->first();

        if (! $access) {
            throw new NotFoundHttpException('Acceso de usuario no encontrado para esta empresa.');
        }

        if ((int) $access->user_id === (int) $actor->user_id && $access->is_default_company) {
            throw new ConflictHttpException('No puedes desactivar tu empresa principal desde esta acción.');
        }

        $access->update([
            'status' => 'INACTIVE',
            'revoked_at' => now(),
        ]);

        return $access->fresh([
            'user:user_id,first_name,last_name,email',
            'role:role_id,name,code',
            'branch:branch_id,name',
        ]);
    }

    public function permissions(int $companyId, int $userId, User $actor): array
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $user = User::query()->find($userId);

        if (! $user) {
            throw new NotFoundHttpException('Usuario no encontrado.');
        }

        return $this->authorizationService
            ->getUserPermissionsInCompany($user, $companyId)
            ->map(fn ($permission) => [
                'permission_id' => $permission->permission_id,
                'code' => $permission->code,
                'name' => $permission->name,
                'module_name' => $permission->module_name,
            ])
            ->values()
            ->all();
    }

    private function ensureActorHasCompanyAccess(int $companyId, User $actor): void
    {
        if (! $this->authorizationService->userHasCompanyAccess($actor, $companyId)) {
            throw new NotFoundHttpException('No tienes acceso activo a esta empresa.');
        }
    }
}
