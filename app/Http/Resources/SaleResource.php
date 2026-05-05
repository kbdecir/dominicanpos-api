<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sale_id' => $this->sale_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'warehouse_id' => $this->warehouse_id,
            'cash_register_id' => $this->cash_register_id,
            'cash_shift_id' => $this->cash_shift_id,
            'customer_id' => $this->customer_id,
            'sold_by_user_id' => $this->sold_by_user_id,
            'sale_number' => $this->sale_number,
            'sale_type' => $this->sale_type,
            'status' => $this->status,
            'sale_datetime' => $this->sale_datetime,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'change_amount' => $this->change_amount,
            'balance_due' => $this->balance_due,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items'),
            'payments' => $this->whenLoaded('payments'),
            'fiscal_document_type_id' => $this->fiscal_document_type_id,
            'ncf_sequence_id' => $this->ncf_sequence_id,
            'ncf' => $this->ncf,
            'fiscal_status' => $this->fiscal_status,
            'fiscal_issued_at' => $this->fiscal_issued_at,
        ];
    }
}
