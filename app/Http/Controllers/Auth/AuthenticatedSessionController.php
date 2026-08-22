<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the storefront login page.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CartService $cart): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /*
         * Whatever the shopper put in their cart as a guest belongs to them now.
         * Runs after regenerate() because the guest cart is keyed off the
         * cart_token cookie, which regenerating the session leaves alone.
         */
        $cart->mergeGuestCartInto($request->user());

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session (log out).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
