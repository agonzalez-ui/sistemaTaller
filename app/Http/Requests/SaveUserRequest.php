<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'username' => ['nullable', 'string', 'max:60', 'alpha_dash:ascii', Rule::unique('users')->ignore($user)],
            'identification_number' => ['nullable', 'string', 'regex:/\A(?:[1-9][0-9]{8}|[1-9][0-9]{10,11})\z/', Rule::unique('users')->ignore($user)],
            'role_id' => ['required', Rule::exists('roles', 'id')->where('active', true)],
            'joined_at' => ['nullable', 'date'],
            'active' => ['required', 'boolean'],
            'verified' => ['required', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'max:255', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre', 'email' => 'correo', 'username' => 'nombre de usuario', 'identification_number' => 'cédula o DIMEX', 'role_id' => 'rol', 'joined_at' => 'fecha de ingreso', 'password' => 'contraseña'];
    }

    public function messages(): array
    {
        return [
            'identification_number.regex' => 'Ingrese una cédula de 9 dígitos o un DIMEX de 11 o 12 dígitos.',
            'role_id.exists' => 'Seleccione un rol activo.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
