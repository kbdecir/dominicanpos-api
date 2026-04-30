<?php

namespace App\Services;

use App\Models\CashRegister;

class CashRegisterService
{
    public function getActiveRegisters(int $companyId, ?int $branchId = null)
    {
        return CashRegister::query()
            ->forCompany($companyId)
            ->when($branchId, fn($query) => $query->forBranch($branchId))
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function findActive(
        int $companyId,
        int $branchId,
        int $cashRegisterId
    ): CashRegister {
        return CashRegister::query()
            ->forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->where('cash_register_id', $cashRegisterId)
            ->firstOrFail();
    }
}
