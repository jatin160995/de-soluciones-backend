<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * The cart page plus the JSON endpoints behind the "Agregar al carrito"
 * buttons and the quantity steppers.
 *
 * None of this sits behind `auth`: guests are the primary audience of a
 * pago-contra-entrega store, so the cart has to work before login and merge
 * into the customer's own cart afterwards.
 *
 * Every response carries server-computed, pre-formatted money strings. The
 * browser is never told a raw unit price it might multiply itself.
 */
class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('cart', [
            'lines'   => $lines,
            'summary' => $this->cart->summary($lines),
            'notice'  => $this->cart->notice(),
        ]);
    }

    /**
     * Cheap read used to re-sync the header badge after a bfcache restore.
     */
    public function summary(): JsonResponse
    {
        return $this->cartResponse();
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));

        $variant = $request->filled('variant_id')
            ? ProductVariant::findOrFail($request->integer('variant_id'))
            : null;

        try {
            $item = $this->cart->add($product, $variant, $request->integer('quantity'));
        } catch (CartException $e) {
            return $this->failure($e->getMessage());
        }

        return $this->cartResponse('Producto agregado al carrito', $item);
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        // Clamps rather than rejects — the corrected number is visible in the
        // stepper, and the reason comes back in `notice`.
        $this->cart->updateQuantity($item, $request->integer('quantity'));

        return $this->cartResponse(null, $item);
    }

    public function destroy(CartItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $this->cart->remove($item);

        return $this->cartResponse('Producto eliminado del carrito');
    }

    public function clear(): JsonResponse
    {
        $this->cart->clear();

        return $this->cartResponse('Tu carrito quedó vacío');
    }

    /**
     * Lines are bound by id, so ownership has to be checked by hand. 404 rather
     * than 403 so ids stay unenumerable — a guest guessing another shopper's
     * item id learns nothing about whether it exists.
     */
    protected function authorizeItem(CartItem $item): void
    {
        $cart = $this->cart->current();

        abort_if(! $cart || $item->cart_id !== $cart->id, 404);
    }

    /**
     * One response shape for every mutation, so the JS has a single code path:
     * `cart` for the badge and summary panel, `item` for the touched line
     * (null once it's gone), `notice` for anything the server corrected.
     */
    protected function cartResponse(?string $message = null, ?CartItem $item = null): JsonResponse
    {
        $lines = $this->cart->lines();

        return response()->json([
            'ok'      => true,
            'message' => $message,
            'cart'    => $this->cart->summary($lines),
            'item'    => $item ? $lines->firstWhere('id', $item->id) : null,
            'notice'  => $this->cart->notice(),
        ]);
    }

    protected function failure(string $message): JsonResponse
    {
        return response()->json([
            'ok'      => false,
            'message' => $message,
        ], 422);
    }
}
