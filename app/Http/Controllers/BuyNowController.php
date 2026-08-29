<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Http\Requests\Checkout\BuyNowRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BuyNowController extends Controller
{
    protected const SESSION_KEY = 'checkout.last_order_id';

    public function __construct(
        protected CheckoutService $checkout
    ) {}

    public function store(BuyNowRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /*
         * -------------------------------------------------------------
         * Product
         * -------------------------------------------------------------
         */
        $product = Product::query()
            ->where('status', 'active')
            ->whereKey($data['product_id'])
            ->firstOrFail();

        /*
         * -------------------------------------------------------------
         * Variant
         * -------------------------------------------------------------
         *
         * Never trust price/SKU/variant attributes from the browser.
         * We resolve the real variant from the database.
         */
        $hasVariants = $product->variants()
            ->where('is_active', true)
            ->exists();

        $variant = null;

        if ($hasVariants) {

            if (empty($data['variant_id'])) {

                return redirect()
                    ->route('product.show', [
                        'slug' => $product->slug,
                    ])
                    ->withErrors([
                        'buy_now' =>
                        'Selecciona una opción del producto antes de continuar.',
                    ], 'buy_now')
                    ->withInput();
            }

            $variant = ProductVariant::query()
                ->whereKey($data['variant_id'])
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();

            if (! $variant) {

                return redirect()
                    ->route('product.show', [
                        'slug' => $product->slug,
                    ])
                    ->withErrors([
                        'buy_now' =>
                        'La opción seleccionada ya no está disponible.',
                    ], 'buy_now')
                    ->withInput();
            }
        }

        /*
         * -------------------------------------------------------------
         * Customer
         * -------------------------------------------------------------
         */
        $user = Auth::user();

        /*
         * -------------------------------------------------------------
         * Address
         * -------------------------------------------------------------
         *
         * If logged-in customer selected a saved address, load the
         * address from the database.
         *
         * Otherwise use the address submitted by the form.
         */
        if ($user && ! empty($data['address_id'])) {

            $savedAddress = $user->addresses()
                ->whereKey($data['address_id'])
                ->firstOrFail();

            $address = [
                'line1'       => $savedAddress->line1,
                'line2'       => $savedAddress->line2,
                'city'        => $savedAddress->city,
                'state'       => $savedAddress->state,
                'postal_code' => $savedAddress->postal_code,
                'country'     => $savedAddress->country ?: 'Honduras',
            ];
        } else {

            $address = [
                'line1'       => $data['line1'],
                'line2'       => $data['line2'] ?? null,
                'city'        => $data['city'],
                'state'       => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country'     => 'Honduras',
            ];
        }

        /*
         * -------------------------------------------------------------
         * Place order
         * -------------------------------------------------------------
         */
        try {

            $order = $this->checkout->placeSingleItem(
                product: $product,

                variant: $variant,

                quantity: (int) $data['quantity'],

                customer: [
                    'name'            => $data['customer_name'],
                    'phone'           => $data['customer_phone'],
                    'whatsapp_number' => $data['whatsapp_number'],
                    'alternate_phone' => $data['alternate_phone'] ?? null,
                ],

                address: $address,

                preferredCourier: $data['preferred_courier'] ?? null,

                user: $user,
            );
        } catch (CartException $e) {

            return redirect()
                ->route('product.show', [
                    'slug' => $product->slug,
                ])
                ->withErrors([
                    'buy_now' => $e->getMessage(),
                ], 'buy_now')
                ->withInput();
        }

        /*
         * -------------------------------------------------------------
         * Reuse the existing confirmation page.
         * -------------------------------------------------------------
         */
        session([
            self::SESSION_KEY => $order->id,
        ]);

        return redirect()->route(
            'checkout.confirmation'
        );
    }
}
