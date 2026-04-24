<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCompanyRole;
use Illuminate\Support\Collection;

class AuthorizationService
{
    public function getUserCompanyAccess(User $user, int $companyId): ?UserCompanyRole
    {
        return UserCompanyRole::query()
            ->with(['role.permissions'])
            ->where('user_id', $user->user_id)
            ->where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->first();
    }

    public function getUserRolesInCompany(User $user, int $companyId): Collection
    {
        return UserCompanyRole::query()
            ->with('role.permissions')
            ->where('user_id', $user->user_id)
            ->where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->get()
            ->pluck('role')
            ->filter()
            ->unique('role_id')
            ->values();
    }

    public function getUserPermissionsInCompany(User $user, int $companyId): Collection
    {
        return $this->getUserRolesInCompany($user, $companyId)
            ->flatMap(fn ($role) => $role->permissions ?? collect())
            ->filter()
            ->unique('permission_id')
            ->values();
    }

    public function userHasCompanyAccess(User $user, int $companyId): bool
    {
        return $this->getUserCompanyAccess($user, $companyId) !== null;
    }
}
