<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stock_movement_id' => $this->stock_movement_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'movement_type' => $this->movement_type,
            'reference_table' => $this->reference_table,
            'reference_id' => $this->reference_id,
            'qty_in' => $this->qty_in,
            'qty_out' => $this->qty_out,
            'unit_cost' => $this->unit_cost,
            'notes' => $this->notes,
            'moved_at' => $this->moved_at,
            'created_by_user_id' => $this->created_by_user_id,

            'product' => $this->whenLoaded('product'),
            'warehouse' => $this->whenLoaded('warehouse'),
        ];
    }
}
