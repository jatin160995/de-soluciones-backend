<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Places storefront orders. Both checkout paths funnel through here so stock
 * locking, price snapshotting, and Order/OrderItem creation only exist once:
 *   - placeFromCart()   -> full cart checkout (checkout.html)
 *   - placeSingleItem() -> "Comprar ahora" quick checkout from the product
 *                          page modal, built for Meta Ads traffic
 *
 * Every order created here is source = 'storefront' and payment_method =
 * 'cod' (Phase 1 / M3 scope - gateways are M4). Guests never get a row in
 * `addresses` (its user_id is NOT NULL by design); their address only ever
 * lives in orders.shipping_snapshot.
 */
class CheckoutService
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Full checkout from the shopper's cart.
     *
     * @param  array{name:string,phone:string,email?:string}  $customer
     * @param  array{recipient_name?:string,phone:string,line1:string,line2?:string,city:string,state?:string,postal_code?:string,country?:string,label?:string}  $address
     *
     * @throws CartException when the cart is empty or something in it is no longer purchasable
     */
    public function placeFromCart(
        Cart $cart,
        array $customer,
        array $address,
        string $shippingMethod,
        ?User $user = null,
        ?string $couponCode = null,
        bool $saveAddress = false,
        ?int $addressId = null,
    ): Order {
        return DB::transaction(function () use ($cart, $customer, $address, $shippingMethod, $user, $couponCode, $saveAddress, $addressId) {
            $items = $cart->items()
                ->with(['product', 'variant'])
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new CartException('Tu carrito está vacío.');
            }

            $lines = $items->map(function ($item) {
                $this->assertPurchasable($item->product, $item->variant, (int) $item->quantity);

                return [
                    'product'    => $item->product,
                    'variant'    => $item->variant,
                    'quantity'   => (int) $item->quantity,
                    'unit_price' => (float) ($item->variant?->effective_price ?? $item->product->effective_price),
                ];
            });

            $subtotal = round($lines->sum(fn($l) => $l['unit_price'] * $l['quantity']), 2);
            $shippingCost = $this->shippingCostFor($shippingMethod);
            $discountPercent = $this->discountPercentFor($couponCode, $subtotal);

            [$snapshot, $resolvedAddressId] = $this->resolveAddress($user, $address, $addressId, $saveAddress, $shippingMethod);

            $order = $this->createOrder([
                'store_id'          => Store::query()->value('id') ?? 1,
                'user_id'           => $user?->id,
                'address_id'        => $resolvedAddressId,
                'shipping_snapshot' => $snapshot,
                'subtotal'          => $subtotal,
                'shipping_cost'     => $shippingCost,
                'discount_percent'  => $discountPercent,
                'payment_method'    => 'cod',
                'source'            => 'storefront',
                'customer_name'     => $customer['name'],
                'customer_phone'    => $customer['phone'],
                'customer_email'    => $customer['email'] ?? null,
            ]);

            $this->createOrderItems($order, $lines);
            $this->cartService->clear();

            if ($couponCode) {
                $this->redeemCoupon($couponCode);
            }

            return $order;
        });
    }

    /**
     * Single-product "Comprar ahora" checkout from the product page modal -
     * no cart, no shipping-method choice (always standard/free), no coupon
     * field. Built for Meta Ads traffic landing straight on a product page.
     *
     * @param  array{name:string,phone:string,whatsapp_number?:string,alternate_phone?:string}  $customer
     * @param  array{line1:string,line2?:string,city:string,state?:string}  $address
     *
     * @throws CartException when the product/variant can't cover the requested quantity
     */
    public function placeSingleItem(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        array $customer,
        array $address,
        ?string $preferredCourier = null,
        ?User $user = null,
    ): Order {
        return DB::transaction(function () use ($product, $variant, $quantity, $customer, $address, $preferredCourier, $user) {
            if ($variant) {
                $variant = ProductVariant::whereKey($variant->id)->lockForUpdate()->first();
            }

            $this->assertPurchasable($product, $variant, $quantity);

            $unitPrice = (float) ($variant?->effective_price ?? $product->effective_price);
            $subtotal = round($unitPrice * $quantity, 2);

            $snapshot = array_filter([
                'recipient_name'    => $customer['name'],
                'phone'             => $customer['phone'],
                'whatsapp_number'   => $customer['whatsapp_number'] ?? null,
                'alternate_phone'   => $customer['alternate_phone'] ?? null,
                'line1'             => $address['line1'],
                'line2'             => $address['line2'] ?? null,
                'city'              => $address['city'],
                'state'             => $address['state'] ?? null,
                'country'           => 'Honduras',
                'preferred_courier' => $preferredCourier,
                'shipping_method'   => 'standard',
                'channel'           => 'buy_now',
            ], fn($v) => $v !== null && $v !== '');

            $order = $this->createOrder([
                'store_id'          => Store::query()->value('id') ?? 1,
                'user_id'           => $user?->id,
                'address_id'        => null,
                'shipping_snapshot' => $snapshot,
                'subtotal'          => $subtotal,
                'shipping_cost'     => $this->shippingCostFor('standard'),
                'discount_percent'  => 0,
                'payment_method'    => 'cod',
                'source'            => 'storefront',
                'customer_name'     => $customer['name'],
                'customer_phone'    => $customer['phone'],
                'customer_email'    => null,
            ]);

            $this->createOrderItems($order, collect([[
                'product'    => $product,
                'variant'    => $variant,
                'quantity'   => $quantity,
                'unit_price' => $unitPrice,
            ]]));

            return $order;
        });
    }

    /**
     * L. 0 (included in the product price) or L. 85 - pulled from
     * site_settings so Daniel can adjust either without a deploy.
     */
    public function shippingCostFor(string $method): float
    {
        return $method === 'express'
            ? (float) SiteSetting::get('express_shipping_cost', 85)
            : (float) SiteSetting::get('standard_shipping_cost', 0);
    }

    /**
     * One place both checkout paths build the Order row, so they share the
     * same defaults. Order's own saving() hook recomputes discount_amount and
     * total from subtotal/shipping_cost/discount_percent - nothing here
     * duplicates that math.
     */
    protected function createOrder(array $attributes): Order
    {
        $order = new Order(array_merge([
            'order_number' => $this->generateOrderNumber(),
            'status'       => 'pending',
        ], $attributes));

        $order->save();

        return $order;
    }

    /**
     * @param  Collection<int, array{product: Product, variant: ?ProductVariant, quantity: int, unit_price: float}>  $lines
     */
    protected function createOrderItems(Order $order, Collection $lines): void
    {
        foreach ($lines as $line) {
            /** @var Product $product */
            $product = $line['product'];
            /** @var ProductVariant|null $variant */
            $variant = $line['variant'];
            $quantity = $line['quantity'];
            $unitPrice = $line['unit_price'];

            OrderItem::create([
                'order_id'           => $order->id,
                'variant_id'         => $variant?->id,
                'product_name'       => $product->name,
                'variant_attributes' => $variant?->attributes,
                'sku'                => $variant?->sku,
                'unit_price'         => $unitPrice,
                'base_unit_price'    => $unitPrice,
                'quantity'           => $quantity,
                'line_total'         => round($unitPrice * $quantity, 2),
            ]);

            if ($variant) {
                $variant->decrement('stock_quantity', $quantity);

                InventoryMovement::create([
                    'variant_id'     => $variant->id,
                    'type'           => 'out',
                    'quantity'       => $quantity,
                    'reason'         => 'Venta en tienda (pedido #' . $order->order_number . ')',
                    'reference_type' => Order::class,
                    'reference_id'   => $order->id,
                    'created_by'     => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Same rules CartService applies when building lines(): the product must
     * be active and not soft-deleted, the variant (if any) must be active and
     * belong to the product, and there must be enough stock. A product with
     * no variant has no stock column and is treated as always available -
     * same rule the cart already follows.
     *
     * @throws CartException
     */
    protected function assertPurchasable(?Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if (! $product || $product->status !== 'active' || $product->trashed()) {
            throw new CartException('Uno de los productos de tu pedido ya no está disponible.');
        }

        if ($variant) {
            if (! $variant->is_active || $variant->product_id !== $product->id) {
                throw new CartException('La opción seleccionada ya no está disponible.');
            }

            if ((int) $variant->stock_quantity < $quantity) {
                throw new CartException(
                    $variant->stock_quantity > 0
                        ? "Solo quedan {$variant->stock_quantity} unidades disponibles de {$product->name}."
                        : "{$product->name} está agotado por el momento."
                );
            }
        }
    }

    /**
     * @return array{0: array<string, mixed>, 1: ?int} [shipping_snapshot payload, resolved address_id]
     */
    protected function resolveAddress(?User $user, array $address, ?int $addressId, bool $saveAddress, string $shippingMethod): array
    {
        // Logged-in shopper picked one of their saved addresses.
        if ($user && $addressId) {
            $saved = Address::where('user_id', $user->id)->findOrFail($addressId);

            return [$this->snapshotFromAddress($saved, $shippingMethod), $saved->id];
        }

        $resolvedId = null;

        // New address, optionally saved to the account for next time.
        if ($user && $saveAddress) {
            $isFirstAddress = $user->addresses()->doesntExist();

            $saved = $user->addresses()->create([
                'label'          => $address['label'] ?? 'Envío',
                'recipient_name' => $address['recipient_name'] ?? null,
                'phone'          => $address['phone'],
                'line1'          => $address['line1'],
                'line2'          => $address['line2'] ?? null,
                'city'           => $address['city'],
                'state'          => $address['state'] ?? null,
                'postal_code'    => $address['postal_code'] ?? null,
                'country'        => $address['country'] ?? 'Honduras',
                'is_default'     => $isFirstAddress,
            ]);

            $resolvedId = $saved->id;
        }

        $snapshot = array_filter([
            'recipient_name'  => $address['recipient_name'] ?? null,
            'phone'           => $address['phone'],
            'line1'           => $address['line1'],
            'line2'           => $address['line2'] ?? null,
            'city'            => $address['city'],
            'state'           => $address['state'] ?? null,
            'postal_code'     => $address['postal_code'] ?? null,
            'country'         => $address['country'] ?? 'Honduras',
            'shipping_method' => $shippingMethod,
            'channel'         => 'cart_checkout',
        ], fn($v) => $v !== null && $v !== '');

        return [$snapshot, $resolvedId];
    }

    protected function snapshotFromAddress(Address $address, string $shippingMethod): array
    {
        return array_filter([
            'recipient_name'  => $address->recipient_name,
            'phone'           => $address->phone,
            'line1'           => $address->line1,
            'line2'           => $address->line2,
            'city'            => $address->city,
            'state'           => $address->state,
            'postal_code'     => $address->postal_code,
            'country'         => $address->country,
            'shipping_method' => $shippingMethod,
            'channel'         => 'cart_checkout',
        ], fn($v) => $v !== null && $v !== '');
    }

    /**
     * A valid, unexpired, still-available coupon is converted into an
     * equivalent discount_percent so Order's own saving() hook stays the one
     * place that computes discount_amount/total.
     *
     * @throws CartException when a code was entered but isn't valid
     */
    protected function discountPercentFor(?string $couponCode, float $subtotal): float
    {
        if (! $couponCode || $subtotal <= 0) {
            return 0.0;
        }

        $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();

        if (! $coupon || ! $coupon->isValid()) {
            throw new CartException('El cupón ingresado no es válido o ya expiró.');
        }

        $discountAmount = $coupon->discountFor($subtotal);

        return round(($discountAmount / $subtotal) * 100, 4);
    }

    protected function redeemCoupon(string $couponCode): void
    {
        Coupon::where('code', strtoupper(trim($couponCode)))->increment('used_count');
    }

    protected function generateOrderNumber(): string
    {
        do {
            $candidate = 'ORD-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::withTrashed()->where('order_number', $candidate)->exists());

        return $candidate;
    }
}
