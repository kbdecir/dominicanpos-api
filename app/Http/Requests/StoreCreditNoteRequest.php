<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('reason')) {
            $this->merge([
                'reason' => trim((string) $this->input('reason')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'original_sale_id' => [
                'required',
                'integer',
                'exists:sales,sale_id',
            ],
            'fiscal_document_type_id' => [
                'required',
                'integer',
                'exists:fiscal_document_types,fiscal_document_type_id',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.sale_item_id' => [
                'required',
                'integer',
                'exists:sale_items,sale_item_id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.0001',
            ],
        ];
    }
}
