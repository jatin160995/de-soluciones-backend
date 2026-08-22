<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shopper's open cart. Guests own one through `session_token` (a long-lived
 * cart_token cookie, not the Laravel session id); signed-in customers own one
 * through `user_id`. Exactly one of the two is set.
 */
class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
