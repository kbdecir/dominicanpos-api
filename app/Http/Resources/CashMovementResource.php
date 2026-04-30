<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cash_movement_id' => $this->cash_movement_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'cash_shift_id' => $this->cash_shift_id,

            'movement_type' => $this->movement_type,
            'payment_method_id' => $this->payment_method_id,
            'expense_category_id' => $this->expense_category_id,
            'amount' => $this->amount,

            'reference_table' => $this->reference_table,
            'reference_id' => $this->reference_id,
            'notes' => $this->notes,
            'created_by_user_id' => $this->created_by_user_id,
            'created_at' => $this->created_at,

            'cash_shift' => CashShiftResource::make($this->whenLoaded('cashShift')),
            'created_by' => $this->whenLoaded('createdBy'),
        ];
    }
}
