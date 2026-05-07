<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'credit_note_id' => $this->credit_note_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'original_sale_id' => $this->original_sale_id,
            'customer_id' => $this->customer_id,
            'fiscal_document_type_id' => $this->fiscal_document_type_id,
            'ncf_sequence_id' => $this->ncf_sequence_id,
            'ncf' => $this->ncf,
            'reason' => $this->reason,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'issued_at' => $this->issued_at,
            'created_by_user_id' => $this->created_by_user_id,

            'items' => CreditNoteItemResource::collection($this->whenLoaded('items')),
            'original_sale' => $this->whenLoaded('originalSale'),
            'customer' => $this->whenLoaded('customer'),
            'fiscal_document_type' => $this->whenLoaded('fiscalDocumentType'),
        ];
    }
}
