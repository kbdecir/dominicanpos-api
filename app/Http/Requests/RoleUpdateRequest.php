<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'scope' => ['sometimes', 'required', 'string', 'in:COMPANY,BRANCH'],
            'status' => ['sometimes', 'required', 'string', 'in:ACTIVE,INACTIVE'],
        ];
    }
}
