<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_type' => strtoupper((string) $this->input('customer_type', 'INDIVIDUAL')),
            'status' => strtoupper((string) $this->input('status', 'ACTIVE')),
            'tax_id' => $this->input('tax_id')
                ? preg_replace('/[^0-9]/', '', (string) $this->input('tax_id'))
                : null,
        ]);
    }

    public function rules(): array
    {
        $companyId = (int) $this->route('companyId');

        return [
            'customer_code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('customers', 'customer_code')
                    ->where('company_id', $companyId),
            ],
            'customer_type' => ['required', Rule::in(['INDIVIDUAL', 'BUSINESS'])],

            'first_name' => [
                Rule::requiredIf($this->input('customer_type') === 'INDIVIDUAL'),
                'nullable',
                'string',
                'max:100',
            ],
            'last_name' => ['nullable', 'string', 'max:100'],

            'business_name' => [
                Rule::requiredIf($this->input('customer_type') === 'BUSINESS'),
                'nullable',
                'string',
                'max:200',
            ],

            'tax_id' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('customers', 'tax_id')
                    ->where('company_id', $companyId)
                    ->whereNotNull('tax_id'),
            ],

            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
