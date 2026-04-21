<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BranchService
{
    public function listByCompany(Company $company): Collection
    {
        return $company->branches()
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();
    }

    public function findForUser(int $branchId, User $user): Branch
    {
        $branch = Branch::query()
            ->where('id', $branchId)
            ->whereHas('company.users', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('company_user.is_active', true);
            })
            ->with('company')
            ->first();

        if (!$branch) {
            throw new NotFoundHttpException('Sucursal no encontrada o sin acceso.');
        }

        return $branch;
    }

    public function create(array $data, Company $company, User $user): Branch
    {
        if (!$company->is_active) {
            throw new ConflictHttpException('No se pueden crear sucursales en una empresa inactiva.');
        }

        return DB::transaction(function () use ($data, $company, $user) {
            if (($data['is_main'] ?? false) === true) {
                $company->branches()->update(['is_main' => false]);
            }

            $branch = Branch::create([
                ...$data,
                'company_id' => $company->id,
                'is_active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            return $branch->fresh();
        });
    }

    public function update(Branch $branch, array $data, User $user): Branch
    {
        return DB::transaction(function () use ($branch, $data, $user) {
            if (($data['is_main'] ?? false) === true) {
                Branch::query()
                    ->where('company_id', $branch->company_id)
                    ->where('id', '!=', $branch->id)
                    ->update(['is_main' => false]);
            }

            $branch->update([
                ...$data,
                'updated_by' => $user->id,
            ]);

            return $branch->fresh();
        });
    }

    public function toggleStatus(Branch $branch, bool $status, User $user): Branch
    {
        $branch->update([
            'is_active' => $status,
            'updated_by' => $user->id,
        ]);

        return $branch->fresh();
    }
}
