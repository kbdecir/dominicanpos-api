<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RoleService
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {}

    public function createCompanyRole(int $companyId, array $data, User $actor): Role
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $code = Str::upper($data['code']);

        $existing = Role::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();

        if ($existing) {
            throw new ConflictHttpException('Ya existe un rol con ese código para esta empresa.');
        }

        return Role::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'scope' => $data['scope'] ?? 'COMPANY',
            'status' => 'ACTIVE',
            'is_system_role' => false,
            'created_by_user_id' => $actor->user_id,
        ])->fresh([
            'company:company_id,trade_name,legal_name',
            'permissions',
        ]);
    }

    public function updateCompanyRole(int $companyId, int $roleId, array $data, User $actor): Role
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $role = Role::query()
            ->where('role_id', $roleId)
            ->first();

        if (! $role) {
            throw new NotFoundHttpException('Rol no encontrado.');
        }

        if ((int) $role->company_id !== (int) $companyId) {
            throw new ConflictHttpException('Este rol no pertenece a la empresa indicada.');
        }

        if ($role->is_system_role) {
            throw new ConflictHttpException('No puedes editar un rol del sistema.');
        }

        $role->update([
            'name' => $data['name'] ?? $role->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $role->description,
            'scope' => $data['scope'] ?? $role->scope,
            'status' => $data['status'] ?? $role->status,
        ]);

        return $role->fresh([
            'company:company_id,trade_name,legal_name',
            'permissions',
        ]);
    }

    private function ensureActorHasCompanyAccess(int $companyId, User $actor): void
    {
        if (! $this->authorizationService->userHasCompanyAccess($actor, $companyId)) {
            throw new NotFoundHttpException('No tienes acceso activo a esta empresa.');
        }
    }

    public function list(?int $companyId = null): Collection
    {
        return Role::query()
            ->with('company:company_id,trade_name,legal_name')
            ->when($companyId, function ($query) use ($companyId) {
                $query->where(function ($q) use ($companyId) {
                    $q->whereNull('company_id')
                        ->orWhere('company_id', $companyId);
                });
            })
            ->orderBy('company_id')
            ->orderBy('name')
            ->get();
    }

    public function permissions(int $roleId): Collection
    {
        $role = Role::query()
            ->with('permissions')
            ->where('role_id', $roleId)
            ->first();

        if (! $role) {
            throw new NotFoundHttpException('Rol no encontrado.');
        }

        return $role->permissions;
    }

    public function syncPermissions(int $roleId, array $permissionIds): Role
    {
        $role = Role::query()
            ->where('role_id', $roleId)
            ->first();

        if (! $role) {
            throw new NotFoundHttpException('Rol no encontrado.');
        }

        $validPermissionIds = Permission::query()
            ->whereIn('permission_id', $permissionIds)
            ->pluck('permission_id')
            ->all();

        $role->permissions()->sync($validPermissionIds);

        return $role->fresh(['permissions']);
    }
}
