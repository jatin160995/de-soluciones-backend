<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Everything the cart knows how to do. The controller only translates.
 *
 * Two kinds of owner:
 *   - signed-in customer → cart found by `user_id`
 *   - guest             → cart found by `session_token`, which mirrors a
 *                         long-lived `cart_token` cookie
 *
 * The cookie is deliberately not the Laravel session id: SESSION_DRIVER=file
 * with SESSION_LIFETIME=120 would drop a guest cart after two idle hours, and
 * session()->regenerate() at login would rotate it out from under us.
 */
class CartService
{
    /** Matches max="99" on the quantity inputs. */
    public const MAX_QUANTITY = 99;

    protected const COOKIE_NAME = 'cart_token';

    protected const COOKIE_MINUTES = 60 * 24 * 30;

    /**
     * Resolved cart for this request. false = not looked up yet, null = none.
     */
    protected Cart|false|null $cart = false;

    /**
     * Token we queued on this request. request()->cookie() can't see a queued
     * cookie, so a second call within the same request would otherwise create
     * a second cart.
     */
    protected ?string $queuedToken = null;

    /**
     * Why lines() dropped something, phrased for the shopper. Null when nothing
     * was dropped.
     */
    protected ?string $notice = null;

    /**
     * The caller's cart. Only touches the database when $create is true, so the
     * view composer can ask for a count on every page without seeding rows for
     * visitors who never add anything.
     */
    public function current(bool $create = false): ?Cart
    {
        if ($this->cart !== false && ! ($create && $this->cart === null)) {
            return $this->cart;
        }

        $cart = Auth::check()
            ? $this->resolveUserCart(Auth::id(), $create)
            : $this->resolveGuestCart($create);

        return $this->cart = $cart;
    }

    public function notice(): ?string
    {
        return $this->notice;
    }

    /**
     * Number of distinct lines, for the header badge. Never writes.
     */
    public function count(): int
    {
        $cart = $this->current();

        if (! $cart) {
            return 0;
        }

        return $this->sellableItems($cart)->count();
    }

    /**
     * Add a product (optionally a specific variant) to the cart, or bump the
     * quantity of the line that's already there.
     *
     * @throws CartException when the product isn't for sale or stock won't cover it
     */
    public function add(Product $product, ?ProductVariant $variant, int $quantity): CartItem
    {
        $this->assertPurchasable($product, $variant);

        $quantity = max(1, $quantity);
        $max = $this->maxQuantityFor($variant);

        if ($max < 1) {
            throw new CartException('Este producto está agotado por el momento.');
        }

        $cart = $this->current(create: true);

        $item = CartItem::firstOrNew([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
        ]);

        $requested = ($item->quantity ?? 0) + $quantity;

        /*
         * Rejected rather than silently clamped: the shopper asked for a number
         * and deserves to know it wasn't honoured. updateQuantity() on the cart
         * page does clamp, because there the number is visibly corrected.
         */
        if ($requested > $max) {
            throw new CartException($this->stockMessage($max, $item->exists ? (int) $item->quantity : 0));
        }

        $item->quantity = $requested;
        $item->save();

        // Keeps the cart at the top of any "recently active" ordering and gives
        // a prune command something to work from.
        $cart->touch();

        return $item;
    }

    /**
     * Set a line's quantity, clamped to what's actually available. Clamping
     * leaves a notice so the page can explain the corrected number.
     */
    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $max = $this->maxQuantityFor($item->variant);
        $quantity = max(1, $quantity);

        if ($quantity > $max) {
            $quantity = max(1, $max);
            $this->notice = $this->stockMessage($max);
        }

        $item->quantity = $quantity;
        $item->save();
        $item->cart?->touch();

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $cart = $item->cart;
        $item->delete();
        $cart?->touch();
    }

    public function clear(): void
    {
        $cart = $this->current();

        if (! $cart) {
            return;
        }

        $cart->items()->delete();
        $cart->touch();
    }

    /**
     * The cart as the views need it: display strings resolved, prices already
     * formatted, nothing left for the browser to compute.
     *
     * Self-healing — a line whose product was soft-deleted, unpublished, or
     * whose variant was switched off is deleted here and reported through
     * notice(). Otherwise the cart would keep quoting a price for something
     * that can no longer be sold.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function lines(): Collection
    {
        $cart = $this->current();

        if (! $cart) {
            return collect();
        }

        $items = $cart->items()
            ->with(['product.category', 'product.media', 'variant'])
            ->orderBy('id')
            ->get();

        [$stale, $live] = $items->partition(fn (CartItem $item) => ! $this->isSellable($item));

        if ($stale->isNotEmpty()) {
            CartItem::whereIn('id', $stale->pluck('id'))->delete();

            $this->notice = $stale->count() === 1
                ? 'Un producto de tu carrito ya no está disponible y fue retirado.'
                : $stale->count() . ' productos de tu carrito ya no están disponibles y fueron retirados.';
        }

        return $live->map(fn (CartItem $item) => $this->presentLine($item))->values();
    }

    /**
     * Totals for the badge, the summary panel, and every JSON response.
     *
     * @param  Collection<int, array<string, mixed>>|null  $lines  reuse of an already-built lines()
     * @return array{lineCount: int, unitCount: int, subtotal: float, subtotalFormatted: string}
     */
    public function summary(?Collection $lines = null): array
    {
        $lines ??= $this->lines();
        $subtotal = round((float) $lines->sum('lineTotal'), 2);

        return [
            'lineCount'         => $lines->count(),
            'unitCount'         => (int) $lines->sum('quantity'),
            'subtotal'          => $subtotal,
            'subtotalFormatted' => $this->formatMoney($subtotal),
        ];
    }

    /**
     * Fold the guest cart into the customer's own on login/register, then get
     * rid of every trace of the guest one.
     *
     * Quantities are summed on collision — the shopper added the product twice
     * in two contexts and meant it both times — then re-clamped, because stock
     * may have moved since either add.
     */
    public function mergeGuestCartInto(User $user): void
    {
        $token = $this->token();

        // Whatever we resolved before the login belongs to the guest.
        $this->cart = false;

        $guestCart = $token
            ? Cart::where('session_token', $token)->latest('id')->first()
            : null;

        if (! $guestCart) {
            $this->forgetToken();

            return;
        }

        $userCart = $this->resolveUserCart($user->id, create: true);

        if ($guestCart->is($userCart)) {
            $this->forgetToken();

            return;
        }

        DB::transaction(function () use ($guestCart, $userCart) {
            $guestItems = $guestCart->items()->with('variant')->get();

            foreach ($guestItems as $guestItem) {
                $target = CartItem::firstOrNew([
                    'cart_id'    => $userCart->id,
                    'product_id' => $guestItem->product_id,
                    'variant_id' => $guestItem->variant_id,
                ]);

                $max = $this->maxQuantityFor($guestItem->variant);
                $merged = ($target->quantity ?? 0) + $guestItem->quantity;

                $target->quantity = max(1, min($merged, $max));
                $target->save();
            }

            // cart_items cascade on the FK, but delete them explicitly so the
            // behaviour doesn't depend on the storage engine.
            $guestCart->items()->delete();
            $guestCart->delete();
        });

        $this->cart = $userCart;
        $this->forgetToken();
    }

    /**
     * A cart line shaped for Blade and for the JSON responses.
     *
     * @return array<string, mixed>
     */
    protected function presentLine(CartItem $item): array
    {
        $product = $item->product;
        $unitPrice = $this->unitPriceFor($item);
        $lineTotal = round($unitPrice * $item->quantity, 2);

        return [
            'id'                 => $item->id,
            'productId'          => $product->id,
            'variantId'          => $item->variant_id,
            'name'               => $product->name,
            'url'                => route('product.show', $product->slug),
            'category'           => $product->category?->name,
            'image'              => $product->getFirstMediaUrl('images', 'thumb'),
            'variantLabel'       => $this->variantLabel($item->variant),
            'quantity'           => (int) $item->quantity,
            'maxQuantity'        => max(1, $this->maxQuantityFor($item->variant)),
            'unitPrice'          => $unitPrice,
            'unitPriceFormatted' => $this->formatMoney($unitPrice),
            'lineTotal'          => $lineTotal,
            'lineTotalFormatted' => $this->formatMoney($lineTotal),
        ];
    }

    /**
     * Price is always read live off the product/variant, never snapshotted —
     * that's order_items' job. Both accessors return strings (decimal:2 cast),
     * hence the float cast.
     */
    protected function unitPriceFor(CartItem $item): float
    {
        return (float) ($item->variant?->effective_price ?? $item->product->effective_price);
    }

    /**
     * "Processor: M1 · Size: 13" · Color: Mid night" — matches the mockup's
     * .cart-item-variant line. Null for variant-less products.
     */
    protected function variantLabel(?ProductVariant $variant): ?string
    {
        if (! $variant) {
            return null;
        }

        $parts = collect($variant->attributes ?? [])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, $key) => Str::ucfirst((string) $key) . ': ' . $value)
            ->values();

        return $parts->isEmpty()
            ? ($variant->sku ?: null)
            : $parts->implode(' · ');
    }

    /**
     * @throws CartException
     */
    protected function assertPurchasable(Product $product, ?ProductVariant $variant): void
    {
        if ($product->status !== 'active' || $product->trashed()) {
            throw new CartException('Este producto ya no está disponible.');
        }

        if ($variant && (! $variant->is_active || $variant->product_id !== $product->id)) {
            throw new CartException('La opción seleccionada ya no está disponible.');
        }
    }

    /**
     * `products` has no stock column — only variants track inventory — so a
     * variant-less product is treated as available and capped by the input's
     * own max. Same rule product.blade.php already applies to its buy box.
     */
    protected function maxQuantityFor(?ProductVariant $variant): int
    {
        if (! $variant) {
            return self::MAX_QUANTITY;
        }

        return min(self::MAX_QUANTITY, max(0, (int) $variant->stock_quantity));
    }

    protected function stockMessage(int $max, int $alreadyInCart = 0): string
    {
        if ($alreadyInCart > 0) {
            return $alreadyInCart >= $max
                ? "Ya tienes el máximo disponible ({$max}) de este producto en tu carrito."
                : "Solo quedan {$max} unidades y ya tienes {$alreadyInCart} en tu carrito.";
        }

        return $max === 1
            ? 'Solo queda 1 unidad disponible de este producto.'
            : "Solo quedan {$max} unidades disponibles de este producto.";
    }

    /**
     * Lines that can still legitimately be sold. Kept as a query so count()
     * stays a single cheap COUNT instead of hydrating the cart.
     */
    protected function sellableItems(Cart $cart): Builder
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            // whereHas on a soft-deleting model already excludes trashed rows.
            ->whereHas('product', fn (Builder $q) => $q->where('status', 'active'))
            ->where(fn (Builder $q) => $q
                ->whereNull('variant_id')
                ->orWhereHas('variant', fn (Builder $v) => $v->where('is_active', true)));
    }

    /**
     * In-memory twin of sellableItems(), for rows already loaded.
     */
    protected function isSellable(CartItem $item): bool
    {
        $product = $item->product;

        if (! $product || $product->status !== 'active') {
            return false;
        }

        if ($item->variant_id && ! $item->variant?->is_active) {
            return false;
        }

        return true;
    }

    protected function resolveUserCart(int $userId, bool $create): ?Cart
    {
        // No unique index on user_id, so take the newest if history left more
        // than one behind.
        $cart = Cart::where('user_id', $userId)->latest('id')->first();

        if ($cart || ! $create) {
            return $cart;
        }

        return Cart::create(['user_id' => $userId]);
    }

    protected function resolveGuestCart(bool $create): ?Cart
    {
        $token = $this->token();

        if ($token) {
            $cart = Cart::whereNull('user_id')
                ->where('session_token', $token)
                ->latest('id')
                ->first();

            if ($cart) {
                return $cart;
            }
        }

        if (! $create) {
            return null;
        }

        // Reuse a token we already handed out this request; otherwise mint one.
        $token ??= $this->issueToken();

        return Cart::create(['session_token' => $token]);
    }

    protected function token(): ?string
    {
        if ($this->queuedToken) {
            return $this->queuedToken;
        }

        $token = request()->cookie(self::COOKIE_NAME);

        return is_string($token) && $token !== '' ? $token : null;
    }

    protected function issueToken(): string
    {
        $token = Str::random(64);

        // Queued cookies attach to JSON responses too, which is how the very
        // first fetch-driven "Agregar" call gets the guest their identity.
        Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_MINUTES);

        return $this->queuedToken = $token;
    }

    protected function forgetToken(): void
    {
        $this->queuedToken = null;
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    protected function formatMoney(float $value): string
    {
        return config('store.currency_symbol', 'L.') . ' ' . number_format($value, 2);
    }
}
