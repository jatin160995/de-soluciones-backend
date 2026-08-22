<?php

namespace App\Http\Requests\Cart;

use App\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    /**
     * Guest checkout is the point of this store — no auth gate here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A grid card posts no quantity at all, and an empty select posts
     * variant_id="" rather than omitting it.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity'   => $this->input('quantity') ?: 1,
            'variant_id' => $this->filled('variant_id') ? $this->input('variant_id') : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('status', 'active')->whereNull('deleted_at')
                ),
            ],

            /*
             * Tying the variant to this product_id is the security-relevant part:
             * without it a crafted request could price product A off product B's
             * cheaper variant.
             */
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where(
                    fn ($query) => $query
                        ->where('product_id', $this->input('product_id'))
                        ->where('is_active', 1)
                ),
            ],

            'quantity' => ['required', 'integer', 'min:1', 'max:' . CartService::MAX_QUANTITY],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'No se indicó el producto.',
            'product_id.exists'   => 'Este producto ya no está disponible.',
            'variant_id.exists'   => 'La opción seleccionada no está disponible para este producto.',
            'quantity.min'        => 'La cantidad debe ser al menos 1.',
            'quantity.max'        => 'La cantidad máxima por producto es ' . CartService::MAX_QUANTITY . '.',
        ];
    }
}
