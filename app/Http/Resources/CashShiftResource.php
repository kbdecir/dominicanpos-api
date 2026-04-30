<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cash_shift_id' => $this->cash_shift_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'cash_register_id' => $this->cash_register_id,
            'cashier_user_id' => $this->cashier_user_id,

            'opening_amount' => $this->opening_amount,
            'expected_cash_amount' => $this->expected_cash_amount,
            'counted_cash_amount' => $this->counted_cash_amount,
            'difference_amount' => $this->difference_amount,

            'total_cash_sales' => $this->total_cash_sales,
            'total_card_sales' => $this->total_card_sales,
            'total_transfer_sales' => $this->total_transfer_sales,
            'total_credit_sales' => $this->total_credit_sales,
            'total_cash_in' => $this->total_cash_in,
            'total_cash_out' => $this->total_cash_out,

            'status' => $this->status,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'cancelled_at' => $this->cancelled_at,

            'opening_notes' => $this->opening_notes,
            'closing_notes' => $this->closing_notes,
            'cancellation_reason' => $this->cancellation_reason,

            'cash_register' => CashRegisterResource::make($this->whenLoaded('cashRegister')),
            /*'cashier' => UserResource::make($this->whenLoaded('cashier')),
            'opened_by' => UserResource::make($this->whenLoaded('openedBy')),
            'closed_by' => UserResource::make($this->whenLoaded('closedBy')),*/
            'cashier' => $this->whenLoaded('cashier'),
            'opened_by' => $this->whenLoaded('openedBy'),
            'closed_by' => $this->whenLoaded('closedBy'),

            'movements' => CashMovementResource::collection($this->whenLoaded('movements')),
        ];
    }
}
