<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\UserCompanyRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyService
{
    public function listForUser(User $user): Collection
    {
        return Company::query()
            ->whereIn('company_id', function ($query) use ($user) {
                $query->select('company_id')
                    ->from('user_company_roles')
                    ->where('user_id', $user->user_id)
                    ->where('status', 'ACTIVE');
            })
            ->orderBy('trade_name')
            ->get();
    }

    public function create(array $data, User $user): Company
    {
        return DB::transaction(function () use ($data, $user) {
            $company = Company::create([
                ...$data,
                'status' => 'ACTIVE',
                'created_by_user_id' => $user->user_id,
            ]);

            return $company->fresh();
        });
    }

    public function findForUser(int $companyId, User $user): Company
    {
        $company = Company::query()
            ->where('company_id', $companyId)
            ->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('user_company_roles')
                    ->whereColumn('user_company_roles.company_id', 'companies.company_id')
                    ->where('user_company_roles.user_id', $user->user_id)
                    ->where('user_company_roles.status', 'ACTIVE');
            })
            ->with('branches')
            ->first();

        if (! $company) {
            throw new NotFoundHttpException('Empresa no encontrada o sin acceso.');
        }

        return $company;
    }

    public function update(Company $company, array $data, User $user): Company
    {
        $company->update([
            ...$data,
        ]);

        return $company->fresh();
    }

    public function toggleStatus(Company $company, bool $status, User $user): Company
    {
        $company->update([
            'status' => $status ? 'ACTIVE' : 'INACTIVE',
        ]);

        return $company->fresh();
    }
}
