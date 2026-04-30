<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cash_register_id' => $this->cash_register_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,

            'open_shift' => CashShiftResource::make($this->whenLoaded('openShift')),
        ];
    }
}
