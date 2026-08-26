<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

#[Fillable(['name', 'email', 'password', 'phone', 'whatsapp_number', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasRoles;
    use HasPanelShield;
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function commissionPlan()
    {
        return $this->hasOne(AgentCommissionPlan::class);
    }

    public function commissionStatements()
    {
        return $this->hasMany(AgentCommissionStatement::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Open carts. There is no unique index on carts.user_id, so this is a
     * hasMany and CartService takes the newest — a customer only ever works
     * with one, but history may have left more than one behind.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class)->latestOfMany();
    }

    /**
     * A storefront shopper (as opposed to admin/staff/sales agents).
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /*
     * ========================================
     * Admin panel isolation
     * ========================================
     *
     * Storefront customers share the `users` table and `web`
     * guard with admins, so we must explicitly keep them out
     * of Filament (/admin).
     *
     * This override is a fail-safe: even if Shield's
     * HasPanelShield boot hook auto-granted `panel_user` to a
     * user created over HTTP (it does, for web signups), anyone
     * carrying the `customer` role is denied here regardless.
     * Signup also syncs roles to just `customer`, so in practice
     * they never keep `panel_user` either.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('customer')) {
            return false;
        }

        return $this->hasRole('super_admin') || $this->hasRole('panel_user') || $this->hasRole('sales_agent');
    }
}
