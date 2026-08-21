<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the email before validation so the unique check and the
     * stored value are always lower-cased.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'           => ['required', 'string', 'max:30'],
            'whatsapp_number' => ['required', 'string', 'max:30'],
            'password'        => ['required', 'confirmed', Password::defaults()],
            'accepts_terms'   => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'            => 'nombre',
            'email'           => 'correo electrónico',
            'phone'           => 'teléfono',
            'whatsapp_number' => 'número de WhatsApp',
            'password'        => 'contraseña',
            'accepts_terms'   => 'términos y condiciones',
        ];
    }
}
