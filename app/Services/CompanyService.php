<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyService
{
    public function listForUser(User $user): Collection
    {
        return $user->companies()
            ->wherePivot('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data, User $user): Company
    {
        return DB::transaction(function () use ($data, $user) {
            $company = Company::create([
                ...$data,
                'is_active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $company->users()->attach($user->id, [
                'role' => 'owner',
                'is_active' => true,
            ]);

            return $company->fresh();
        });
    }

    public function findForUser(int $companyId, User $user): Company
    {
        $company = Company::query()
            ->where('id', $companyId)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('company_user.is_active', true);
            })
            ->with('branches')
            ->first();

        if (!$company) {
            throw new NotFoundHttpException('Empresa no encontrada o sin acceso.');
        }

        return $company;
    }

    public function update(Company $company, array $data, User $user): Company
    {
        $company->update([
            ...$data,
            'updated_by' => $user->id,
        ]);

        return $company->fresh();
    }

    public function toggleStatus(Company $company, bool $status, User $user): Company
    {
        $company->update([
            'is_active' => $status,
            'updated_by' => $user->id,
        ]);

        return $company->fresh();
    }
}
