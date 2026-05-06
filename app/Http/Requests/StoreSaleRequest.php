<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
            'cash_register_id' => ['required', 'integer', 'exists:cash_registers,cash_register_id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,customer_id'],
            'sale_type' => ['nullable', 'in:TICKET,INVOICE,QUOTE'],
            'notes' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,product_id'],
            'items.*.product_name' => ['required', 'string', 'max:180'],
            'items.*.sku' => ['required', 'string', 'max:60'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],

            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', 'integer', 'exists:payment_methods,payment_method_id'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:100'],
            'fiscal_document_type_id' => [
                Rule::requiredIf(fn() => $this->input('sale_type') === 'INVOICE'),
                'nullable',
                'integer',
                'exists:fiscal_document_types,fiscal_document_type_id',
            ],
        ];
    }
}
