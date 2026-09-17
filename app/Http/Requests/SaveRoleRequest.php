<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($this->route('role'))],
            'description' => ['required', 'string', 'max:250'],
            'active' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array:can_view,can_create,can_edit,can_delete'],
            'permissions.*.can_view' => ['sometimes', 'boolean'],
            'permissions.*.can_create' => ['sometimes', 'boolean'],
            'permissions.*.can_edit' => ['sometimes', 'boolean'],
            'permissions.*.can_delete' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre', 'description' => 'descripción', 'active' => 'estado'];
    }
}
