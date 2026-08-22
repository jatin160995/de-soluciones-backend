<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Save a new address for the signed-in customer.
     */
    public function store(AddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            // The first address a customer saves is always their default.
            $data['is_default'] = $data['is_default'] || $user->addresses()->doesntExist();

            $address = $user->addresses()->create($data);

            $this->clearOtherDefaults($address);
        });

        return $this->backToAddresses('Dirección guardada.');
    }

    /**
     * Update one of the customer's own addresses.
     */
    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorizeOwnership($request, $address);

        $data = $request->validated();

        DB::transaction(function () use ($address, $data) {
            // Never leave the customer without a default: un-checking the box
            // on the only/current default is a no-op.
            $data['is_default'] = $data['is_default'] || $address->is_default;

            $address->update($data);

            $this->clearOtherDefaults($address);
        });

        return $this->backToAddresses('Dirección actualizada.');
    }

    /**
     * Delete one of the customer's own addresses.
     */
    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeOwnership($request, $address);

        $user = $request->user();

        DB::transaction(function () use ($user, $address) {
            $wasDefault = $address->is_default;

            $address->delete();

            // Promote the oldest remaining address so one default always exists.
            if ($wasDefault) {
                $user->addresses()
                    ->orderBy('id')
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });

        return $this->backToAddresses('Dirección eliminada.');
    }

    /**
     * Addresses are bound by id, so ownership has to be checked explicitly.
     */
    protected function authorizeOwnership(Request $request, Address $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 403);
    }

    /**
     * Keep `is_default` a single-winner flag per customer.
     */
    protected function clearOtherDefaults(Address $address): void
    {
        if (! $address->is_default) {
            return;
        }

        Address::query()
            ->where('user_id', $address->user_id)
            ->whereKeyNot($address->getKey())
            ->update(['is_default' => false]);
    }

    protected function backToAddresses(string $message): RedirectResponse
    {
        return redirect()
            ->route('account.index', ['tab' => 'direcciones'])
            ->with('status', $message);
    }
}
