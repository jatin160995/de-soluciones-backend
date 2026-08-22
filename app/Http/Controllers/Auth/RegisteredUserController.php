<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show the storefront registration page.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request, CartService $cart): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'],
            'whatsapp_number' => $data['whatsapp_number'],
            'password'        => $data['password'], // hashed by the model's 'password' => 'hashed' cast
            'status'          => 'active',
        ]);

        /*
         * Storefront customers get ONLY the `customer` role. syncRoles()
         * also strips the `panel_user` role that Shield's HasPanelShield
         * boot hook auto-assigns to any user created over HTTP, so a
         * customer can never reach the Filament admin panel.
         */
        $user->syncRoles(['customer']);

        Auth::login($user);

        // Someone who filled a cart and then signed up keeps that cart.
        $cart->mergeGuestCartInto($user);

        return redirect()->route('home');
    }
}
