<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by address create + update. Field names mirror the checkout address
 * block in the HTML prototype, which maps 1:1 onto the addresses table.
 */
class AddressRequest extends FormRequest
{
    /**
     * The account page renders two forms at once, so each keeps its own bag.
     */
    protected $errorBag = 'address';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // The country field is read-only in the UI; never trust the post body.
            'country'    => 'Honduras',
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'label'          => ['nullable', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:30'],
            'line1'          => ['required', 'string', 'max:255'],
            'line2'          => ['required', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:120'],
            'state'          => ['required', 'string', 'max:120'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
            'country'        => ['required', 'string', 'max:120'],
            'is_default'     => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'label'          => 'etiqueta',
            'recipient_name' => 'nombre de quien recibe',
            'phone'          => 'teléfono',
            'line1'          => 'dirección',
            'line2'          => 'referencia de la dirección',
            'city'           => 'ciudad',
            'state'          => 'departamento',
            'postal_code'    => 'código postal',
            'country'        => 'país',
        ];
    }

    /**
     * Bounce validation failures back onto the "Mis direcciones" tab. The view
     * re-opens the modal when the error bag is non-empty.
     */
    protected function getRedirectUrl(): string
    {
        return route('account.index', ['tab' => 'direcciones']);
    }
}
