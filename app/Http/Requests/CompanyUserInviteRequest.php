<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUserInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,branch_id'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
