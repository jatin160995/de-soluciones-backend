<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Field names mirror checkout.html 1:1, same convention as
 * App\Http\Requests\Account\AddressRequest.
 *
 * customer_email is deliberately NOT required - per Daniel: guest checkout
 * has no OTP and email isn't needed for direct (COD) payment. Only 'cod' is
 * accepted for payment_method right now; card/paypal are M4 scope.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country'      => 'Honduras',
            'save_address' => $this->boolean('save_address'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /*
     * A logged-in customer can either:
     *
     * 1. Select one of their saved addresses -> address_id is present
     * 2. Enter a new address manually -> address_id is empty
     *
     * When address_id is present, the manual address fields are not required
     * because CheckoutService will load the selected saved address.
     */
        $hasSavedAddress = $this->filled('address_id');

        return [
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
         */
            'address_id' => [
                'nullable',
                'integer',
            ],

            /*
         * Manual address fields.
         *
         * Required only when the customer is NOT using a saved address.
         */
            'recipient_name' => [
                $hasSavedAddress ? 'nullable' : 'required',
                'string',
                'max:150',
            ],

            'label' => [
                'nullable',
                'string',
                'max:40',
            ],

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
         * Shipping.
         */
            'shipping_method' => [
                'required',
                Rule::in(['standard', 'express']),
            ],

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
         * M3 = COD only.
         */
            'payment_method' => [
                'required',
                Rule::in(['cod']),
            ],

            'coupon_code' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],

            'accept_terms' => [
                'accepted',
            ],

            'save_address' => [
                'boolean',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.in' => 'Este método de pago estará disponible próximamente. Por ahora, elige pago contra entrega.',
            'accept_terms.accepted' => 'Debes aceptar los términos y condiciones para continuar.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name'   => 'nombre completo',
            'customer_phone'  => 'teléfono',
            'whatsapp_number' => 'número de WhatsApp',
            'customer_email'  => 'correo electrónico',
            'recipient_name'  => 'nombre de quien recibe',
            'line1'           => 'dirección',
            'line2'           => 'referencia de la dirección',
            'city'            => 'ciudad',
            'state'           => 'departamento',
            'shipping_method' => 'método de envío',
        ];
    }
}
