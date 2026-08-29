<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Full cart checkout (checkout.html ported to Blade). Deliberately NOT
 * behind `auth`, same reasoning as CartController: pago contra entrega and
 * guest checkout is the primary path for this launch - no OTP gate, per
 * Daniel ("checkout como invitado sin OTP").
 *
 * The new order's id is kept in the session between steps rather than in the
 * URL, so a guest can't be walked through someone else's confirmation page
 * by guessing an id.
 */
class CheckoutController extends Controller
{
    protected const SESSION_KEY = 'checkout.last_order_id';

    public function __construct(protected CartService $cart, protected CheckoutService $checkout) {}

    public function index(): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('status', 'Agrega productos a tu carrito antes de continuar.');
        }

        $user = Auth::user();

        return view('checkout', [
            'lines'            => $lines,
            'summary'          => $this->cart->summary($lines),
            'addresses'        => $user ? $user->addresses()->orderByDesc('is_default')->get() : collect(),
            'standardShipping' => $this->checkout->shippingCostFor('standard'),
            'expressShipping'  => $this->checkout->shippingCostFor('express'),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();
        $cart = $this->cart->current();

        if (! $cart) {
            return redirect()->route('cart.index')
                ->with('status', 'Tu carrito está vacío.');
        }

        try {
            $order = $this->checkout->placeFromCart(
                cart: $cart,
                customer: [
                    'name'  => $data['customer_name'],
                    'phone' => $data['customer_phone'],
                    'email' => $data['customer_email'] ?? null,
                ],
                address: [
                    'recipient_name' => $data['recipient_name'],
                    'label'          => $data['label'] ?? null,
                    'phone'          => $data['customer_phone'],
                    'line1'          => $data['line1'],
                    'line2'          => $data['line2'] ?? null,
                    'city'           => $data['city'],
                    'state'          => $data['state'],
                    'postal_code'    => $data['postal_code'] ?? null,
                    'country'        => 'Honduras',
                ],
                shippingMethod: $data['shipping_method'],
                user: $user,
                couponCode: $data['coupon_code'] ?? null,
                saveAddress: $user ? (bool) ($data['save_address'] ?? false) : false,
                addressId: $user ? ($data['address_id'] ?? null) : null,
            );
        } catch (CartException $e) {
            return redirect()->route('checkout.index')
                ->withErrors(['checkout' => $e->getMessage()])
                ->withInput();
        }

        session([self::SESSION_KEY => $order->id]);

        return redirect()->route('checkout.confirmation');
    }

    public function confirmation(): View|RedirectResponse
    {
        $orderId = session(self::SESSION_KEY);
        $order = $orderId ? Order::with('items')->find($orderId) : null;

        if (! $order) {
            return redirect()->route('home');
        }

        return view('checkout-confirmation', ['order' => $order]);
    }
}