<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon) {
            if ($coupon->code) {
                $coupon->code = strtoupper(trim($coupon->code));
            }
        });
    }

    /**
     * Whether this coupon can currently be redeemed: active, within its date
     * window, and under its usage limit (if one is set). This is what the
     * future checkout flow will call before applying a coupon to an order.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Compute the discount this coupon applies to a given subtotal.
     * Fixed-amount coupons are capped at the subtotal so a discount can
     * never push an order total below zero.
     */
    public function discountFor(float $subtotal): float
    {
        if ($this->type === 'percent') {
            return round($subtotal * ((float) $this->value / 100), 2);
        }

        return round(min((float) $this->value, $subtotal), 2);
    }
}
