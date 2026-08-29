<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BuyNowRequest extends FormRequest
{
    protected $errorBag = 'buy_now';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country' => 'Honduras',
        ]);
    }

    public function rules(): array
    {
        $hasSavedAddress = $this->filled('address_id');

        $addressIdRules = [
            'nullable',
            'integer',
        ];

        if (Auth::check()) {
            $addressIdRules[] = Rule::exists('addresses', 'id')
                ->where(
                    fn($query) => $query->where(
                        'user_id',
                        Auth::id()
                    )
                );
        } else {
            $addressIdRules[] = 'prohibited';
        }

        return [

            /*
             * Product
             */
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'variant_id' => [
                'nullable',
                'integer',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],

            /*
             * Customer
             */
            'customer_name' => [
                'required',
                'string',
                'max:150',
            ],

            'customer_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'whatsapp_number' => [
                'required',
                'string',
                'max:30',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'nullable',
                'email',
                'max:150',
            ],

            /*
             * Saved address.
             *
             * Only authenticated users can send address_id.
             */
            'address_id' => $addressIdRules,

            /*
             * Manual address.
             *
             * These are required only when a saved address
             * has NOT been selected.
             */
            'line1' => [
                $hasSavedAddress ? 'nullable' : 'required',
                'string',
                'max:255',
            ],

            'line2' => [
                $hasSavedAddress ? 'nullable' : 'required',
                'string',
                'max:255',
            ],

            'city' => [
                $hasSavedAddress ? 'nullable' : 'required',
                'string',
                'max:120',
            ],

            'state' => [
                $hasSavedAddress ? 'nullable' : 'required',
                'string',
                'max:120',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'country' => [
                'required',
                'string',
                'max:120',
            ],

            /*
             * Buy Now always uses standard/free shipping.
             */
            'preferred_courier' => [
                'nullable',
                Rule::in([
                    '',
                    'c807',
                    'cargo_expreso',
                    'forza_delivery',
                ]),
            ],

            /*
             * COD only in M3.
             */
            'accept_terms' => [
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' =>
            'No se encontró el producto.',

            'product_id.exists' =>
            'El producto seleccionado ya no está disponible.',

            'variant_id.integer' =>
            'La opción seleccionada no es válida.',

            'quantity.required' =>
            'La cantidad es obligatoria.',

            'quantity.min' =>
            'La cantidad debe ser al menos 1.',

            'quantity.max' =>
            'La cantidad máxima permitida es 99.',

            'accept_terms.accepted' =>
            'Debes aceptar los términos y condiciones para continuar.',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id'      => 'producto',
            'variant_id'      => 'opción del producto',
            'quantity'        => 'cantidad',
            'customer_name'   => 'nombre completo',
            'customer_phone'  => 'teléfono',
            'whatsapp_number' => 'número de WhatsApp',
            'alternate_phone' => 'teléfono alternativo',
            'customer_email'  => 'correo electrónico',
            'address_id'      => 'dirección',
            'line1'           => 'dirección',
            'line2'           => 'referencia de la dirección',
            'city'            => 'ciudad',
            'state'           => 'departamento',
        ];
    }
}
