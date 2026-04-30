<?php

namespace App\Services;

use App\Models\CashShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashShiftService
{
    public function findForBranch(
        int $cashShiftId,
        int $companyId,
        int $branchId
    ): CashShift {
        return CashShift::query()
            ->forCompany($companyId)
            ->forBranch($branchId)
            ->where('cash_shift_id', $cashShiftId)
            ->firstOrFail();
    }
    public function findOpenForBranch(
        int $cashShiftId,
        int $companyId,
        int $branchId
    ): CashShift {
        return CashShift::query()
            ->forCompany($companyId)
            ->forBranch($branchId)
            ->where('cash_shift_id', $cashShiftId)
            ->open()
            ->firstOrFail();
    }
    public function open(array $data): CashShift
    {
        return DB::transaction(function () use ($data) {
            $this->ensureCashierHasNoOpenShift(
                companyId: $data['company_id'],
                userId: $data['cashier_user_id']
            );

            $this->ensureRegisterHasNoOpenShift(
                companyId: $data['company_id'],
                branchId: $data['branch_id'],
                cashRegisterId: $data['cash_register_id']
            );

            return CashShift::create([
                'company_id' => $data['company_id'],
                'branch_id' => $data['branch_id'],
                'cash_register_id' => $data['cash_register_id'],

                'cashier_user_id' => $data['cashier_user_id'],
                'opened_by_user_id' => $data['opened_by_user_id'],

                'opening_amount' => $data['opening_amount'],
                'expected_cash_amount' => $data['opening_amount'],

                'counted_cash_amount' => null,
                'difference_amount' => null,

                'status' => 'OPEN',
                'opened_at' => now(),

                'opening_notes' => $data['opening_notes'] ?? null,
            ]);
        });
    }

    public function currentOpenShift(
        int $companyId,
        int $branchId,
        int $userId
    ): ?CashShift {
        return CashShift::query()
            ->with(['cashRegister', 'cashier', 'movements'])
            ->forCompany($companyId)
            ->forBranch($branchId)
            ->where('cashier_user_id', $userId)
            ->open()
            ->first();
    }

    public function close(CashShift $shift, array $data): CashShift
    {
        return DB::transaction(function () use ($shift, $data) {
            $shift->refresh();

            if (! $shift->isOpen()) {
                throw ValidationException::withMessages([
                    'cash_shift' => 'Este turno ya no está abierto.',
                ]);
            }

            $countedAmount = (float) $data['counted_cash_amount'];
            $expectedAmount = (float) $shift->expected_cash_amount;
            $difference = $countedAmount - $expectedAmount;

            $shift->update([
                'counted_cash_amount' => $countedAmount,
                'difference_amount' => $countedAmount - $expectedAmount,
                'closed_by_user_id' => $data['closed_by_user_id'],
                'closed_at' => now(),
                'status' => 'CLOSED',
                'closing_notes' => $data['closing_notes'] ?? null,
            ]);

            return $shift;
        });
    }

    public function cancel(CashShift $shift, int $cancelledBy, ?string $notes = null): CashShift
    {
        return DB::transaction(function () use ($shift, $cancelledBy, $notes) {
            $shift->refresh();

            if (! $shift->isOpen()) {
                throw ValidationException::withMessages([
                    'cash_shift' => 'Solo se puede cancelar un turno abierto.',
                ]);
            }

            $shift->update([
                'closed_by' => $cancelledBy,
                'closed_at' => now(),
                'status' => 'cancelled',
                'notes' => $notes ?? $shift->notes,
            ]);

            return $shift;
        });
    }

    private function ensureCashierHasNoOpenShift(int $companyId, int $userId): void
    {
        $exists = CashShift::query()
            ->forCompany($companyId)
            ->where('cashier_user_id', $userId)
            ->open()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'user_id' => 'Este cajero ya tiene un turno abierto.',
            ]);
        }
    }

    private function ensureRegisterHasNoOpenShift(
        int $companyId,
        int $branchId,
        int $cashRegisterId
    ): void {
        $exists = CashShift::query()
            ->forCompany($companyId)
            ->forBranch($branchId)
            ->where('cash_register_id', $cashRegisterId)
            ->open()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'cash_register_id' => 'Esta caja ya tiene un turno abierto.',
            ]);
        }
    }
}
