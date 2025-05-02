<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',              // requiere campo password_confirmation
                Password::min(8)          // mínimo 8 caracteres
                    ->mixedCase()         // mayúsculas y minúsculas
                    ->letters()           // al menos una letra
                    ->numbers()           // al menos un número
                    ->symbols(),          // al menos un símbolo
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
