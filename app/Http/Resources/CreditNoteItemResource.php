<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditNoteItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'credit_note_item_id' => $this->credit_note_item_id,
            'credit_note_id' => $this->credit_note_id,
            'sale_item_id' => $this->sale_item_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'tax_amount' => $this->tax_amount,
            'line_total' => $this->line_total,
        ];
    }
}
