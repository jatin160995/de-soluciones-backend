<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Tabs the sidebar can deep-link to. Anything else falls back to orders.
     */
    protected const TABS = ['pedidos', 'direcciones', 'datos'];

    /**
     * Show the customer account page (orders, addresses, personal details).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        /*
         * Guest orders (user_id null) are intentionally not matched by
         * email/phone here — the checkout that creates them does not exist
         * yet, and verificar-pedido.html covers guest lookups separately.
         */
        $orders = $user->orders()
            ->with('items.variant.product.media')
            ->latest()
            ->paginate(5)
            ->appends(['tab' => 'pedidos']);

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        $tab = (string) $request->query('tab', 'pedidos');

        return view('account', [
            'user'      => $user,
            'orders'    => $orders,
            'addresses' => $addresses,
            'activeTab' => in_array($tab, self::TABS, true) ? $tab : 'pedidos',
        ]);
    }

    /**
     * Update the "Datos personales" form.
     *
     * Password changes are deliberately out of scope: the design's profile
     * form has no password fields, and recuperar-password.html owns that flow.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()
            ->fill($request->validated())
            ->save();

        return redirect()
            ->route('account.index', ['tab' => 'datos'])
            ->with('status', 'Tus datos fueron actualizados.');
    }
}
