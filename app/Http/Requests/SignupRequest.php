<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Override;

class SignupRequest extends FormRequest
{
    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Email no válido',
            'email.unique' => 'Este email ya esta registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.confirmed' => 'La contraseña no coinciden',
            'password.min' => 'La contraseña debe tener al menos :min caracteres',
            'password.letters' => 'La contrasela debe tener al menos una letra',
            'password.mixed' => 'La contraseña debe tener al menos 1 letra mayúscula y 1 letra minúscula',
            'password.symbols' => 'La contraseña debe tener al menos 1 caracter especial ($%#")',
            'password.number' => 'La contraseña debe tener al menos 1 número',
            'password.uncompromised' => 'La contraseña ha aparecido en filtraciones de datos. Elige una mas segura.',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
