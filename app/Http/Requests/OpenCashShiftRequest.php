<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El permiso lo valida el middleware permission:cash_shifts.open
    }

    public function rules(): array
    {
        return [
            'cash_register_id' => [
                'required',
                'integer',
                'exists:cash_registers,cash_register_id',
            ],
            'opening_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
