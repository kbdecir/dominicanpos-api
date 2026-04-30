<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleSyncPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,permission_id'],
        ];
    }
}
