<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'inventory_balance_id' => $this->inventory_balance_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'qty_on_hand' => $this->qty_on_hand,
            'avg_cost' => $this->avg_cost,

            'product' => $this->whenLoaded('product'),
            'warehouse' => $this->whenLoaded('warehouse'),
        ];
    }
}
