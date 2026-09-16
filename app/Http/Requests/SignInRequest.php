<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class SignInRequest extends FormRequest
{
    #[Override]
    public function attributes() :array
    {
        return [
            'password' => 'contraseña'
        ];
    }

    /* reecribir los mensajes */
    #[Override]
    public function messages(): array
    {
        return [
            'email.exists' => 'No encontramos una cuenta con ese correo electrónico.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required']
        ];
    }
}
