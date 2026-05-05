<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashMovementService
{
    public function create(CashShift $shift, array $data): CashMovement
    {
        return DB::transaction(function () use ($shift, $data) {
            $shift->refresh();

            if (! $shift->isOpen()) {
                throw ValidationException::withMessages([
                    'cash_shift_id' => 'No se pueden registrar movimientos en un turno cerrado.',
                ]);
            }

            $type = strtoupper($data['type']);
            $amount = (float) $data['amount'];

            if (! in_array($type, ['INCOME', 'EXPENSE'], true)) {
                throw ValidationException::withMessages([
                    'type' => 'El tipo de movimiento debe ser INCOME o EXPENSE.',
                ]);
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'El monto debe ser mayor que cero.',
                ]);
            }

            $movement = CashMovement::create([
                'company_id' => $shift->company_id,
                'branch_id' => $shift->branch_id,
                'cash_shift_id' => $shift->cash_shift_id,
                'movement_type' => $type === 'INCOME' ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT',
                'payment_method_id' => null,
                'expense_category_id' => null,
                'amount' => $amount,
                'reference_table' => null,
                'reference_id' => null,
                'notes' => $data['notes'] ?? $data['reason'],
                'created_by_user_id' => $data['created_by_user_id'],
            ]);

            /* $expectedAmount = (float) $shift->expected_cash_amount;

            $shift->update([
                'expected_cash_amount' => $type === 'INCOME'
                    ? $expectedAmount + $amount
                    : $expectedAmount - $amount,
            ]); */

            $expectedAmount = (float) $shift->expected_cash_amount;

            /* if ($type === 'EXPENSE') {
                $newExpected = $expectedAmount - $amount;

                if ($newExpected < 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'No se puede registrar una salida que deje el efectivo esperado en negativo.',
                    ]);
                }
            } else {
                $newExpected = $expectedAmount + $amount;
            } */
            $minCashBalance = (float) $shift->cashRegister()->value('min_cash_balance');

            if ($type === 'EXPENSE') {
                $newExpected = $expectedAmount - $amount;

                if ($newExpected < $minCashBalance) {
                    throw ValidationException::withMessages([
                        'amount' => "No se puede registrar una salida que deje el efectivo esperado por debajo del mínimo permitido ({$minCashBalance}).",
                    ]);
                }
            } else {
                $newExpected = $expectedAmount + $amount;
            }

            $shift->update([
                'expected_cash_amount' => $newExpected,
            ]);

            return $movement->fresh(['cashShift', 'user', 'createdBy']);
        });
    }
}
