<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line in a cart.
 *
 * `product_id` is always set. `variant_id` is only set when the product
 * actually has variants — most of this catalogue doesn't, which is why the
 * column is nullable (see the make_cart_items_product_aware migration).
 *
 * Unlike `order_items`, nothing is snapshotted here: a cart should always show
 * today's price, so price and stock are read live off the product/variant.
 */
class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * `variant_key` is a STORED generated column (IFNULL(variant_id, 0)) that
     * backs the uniqueness constraint. The database owns it — writing to it
     * from PHP is an error, so keep it out of mass assignment entirely.
     */
    protected $guarded = [
        'id',
        'variant_key',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
