<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('customer_type')) {
            $this->merge([
                'customer_type' => strtoupper((string) $this->input('customer_type')),
            ]);
        }

        if ($this->has('status')) {
            $this->merge([
                'status' => strtoupper((string) $this->input('status')),
            ]);
        }

        if ($this->has('tax_id')) {
            $this->merge([
                'tax_id' => $this->input('tax_id')
                    ? preg_replace('/[^0-9]/', '', (string) $this->input('tax_id'))
                    : null,
            ]);
        }
    }

    public function rules(): array
    {
        $companyId = (int) $this->route('companyId');
        $customerId = (int) $this->route('customerId');

        return [
            'customer_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                Rule::unique('customers', 'customer_code')
                    ->where('company_id', $companyId)
                    ->ignore($customerId, 'customer_id'),
            ],
            'customer_type' => ['sometimes', Rule::in(['INDIVIDUAL', 'BUSINESS'])],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'business_name' => ['sometimes', 'nullable', 'string', 'max:200'],

            'tax_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                Rule::unique('customers', 'tax_id')
                    ->where('company_id', $companyId)
                    ->whereNotNull('tax_id')
                    ->ignore($customerId, 'customer_id'),
            ],

            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address_line_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'province' => ['sometimes', 'nullable', 'string', 'max:100'],
            'credit_limit' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
