<?php

namespace App\Http\Requests\Cart;

use App\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    /**
     * Ownership of the line is checked in the controller against the caller's
     * own cart — it can't be expressed here, since guests have no user id.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:' . CartService::MAX_QUANTITY],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Indica una cantidad.',
            'quantity.min'      => 'La cantidad debe ser al menos 1.',
            'quantity.max'      => 'La cantidad máxima por producto es ' . CartService::MAX_QUANTITY . '.',
        ];
    }
}
