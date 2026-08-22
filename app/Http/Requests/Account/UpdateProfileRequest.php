<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * The account page renders two forms at once, so each keeps its own bag.
     */
    protected $errorBag = 'profile';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the email the same way RegisterRequest does, so the unique
     * check and the stored value are always lower-cased.
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
            'email'           => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone'           => ['required', 'string', 'max:30'],
            'whatsapp_number' => ['required', 'string', 'max:30'],
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
        ];
    }

    /**
     * Bounce validation failures back onto the "Datos personales" tab instead
     * of the default "Mis pedidos" one.
     */
    protected function getRedirectUrl(): string
    {
        return route('account.index', ['tab' => 'datos']);
    }
}
