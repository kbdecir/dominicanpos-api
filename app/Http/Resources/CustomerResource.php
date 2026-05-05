<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'customer_id' => $this->customer_id,
            'company_id' => $this->company_id,
            'customer_code' => $this->customer_code,
            'customer_type' => $this->customer_type,
            'display_name' => $this->display_name,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'business_name' => $this->business_name,
            'tax_id' => $this->tax_id,

            'email' => $this->email,
            'phone' => $this->phone,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'province' => $this->province,

            'credit_limit' => $this->credit_limit,
            'balance' => $this->balance,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
