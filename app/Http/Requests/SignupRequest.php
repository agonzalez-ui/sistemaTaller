<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Override;

class SignupRequest extends FormRequest {
  

 

    #[Override]
    public function messages() :array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es bligatorio',
            'email.email' => 'Email no válido',
            'email.unique' => 'Este email ya esta registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.confirmed' => 'La contraseña no coinciden',
            'password.min' => 'La contraseña debe tener al menos :min caracteres',
            'password.letters' => 'La contrasela debe tener al menos una letra',
            'password.mixed' => 'La contraseña debe tener al menos 1 letra mayúscula y 1 letra minúscula',
            'password.symbols' => 'La contraseña debe tener al menos 1 caracter especial ($%#")',
            'password.number' => 'La contraseña debe tener al menos 1 número',
            'password.uncompromised' => 'La contraseña ha aparecido en filtraciones de datos. Elige una mas segura.'
         ];
    }

    public function rules(): array {
        return [
            'name' => [ 'required', 'string' ],
            'email' => [ 'required', 'email','unique:users,email' ],
            'password' => ['required', 'confirmed', 
                Password::min(4)
                    /* ->letters()
                    ->mixedCase()
                    ->symbols()
                    ->numbers()
                    ->uncompromised() */
            ]
        ];
    }
}
