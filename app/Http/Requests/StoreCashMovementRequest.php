<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El permiso lo valida el middleware permission:cash_movements.create
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('type')) {
            $this->merge([
                'type' => strtoupper((string) $this->input('type')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(['INCOME', 'EXPENSE']),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
