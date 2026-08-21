<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Ensure the storefront `customer` role exists on the `web` guard.
     *
     * Storefront signups are synced to this role only, which keeps them
     * out of the Filament admin panel (see User::canAccessPanel()).
     *
     * Idempotent — safe to run on every environment (local, staging, prod).
     */
    public function run(): void
    {
        // Make sure we read/write fresh permission data, not a stale cache.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('customer', 'web');
    }
}
