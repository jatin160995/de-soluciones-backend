<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'variant_id',
        'product_name',
        'variant_attributes',
        'sku',
        'unit_price',
        'base_unit_price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'unit_price' => 'decimal:2',
        'base_unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * How much above the real/base price this line item was sold for, per unit.
     * Only meaningful for manual orders where an agent set a custom sale price;
     * returns 0 when there's no markup or no base price was recorded.
     */
    public function markupPerUnit(): float
    {
        if ($this->base_unit_price === null) {
            return 0.0;
        }

        return max(0.0, (float) $this->unit_price - (float) $this->base_unit_price);
    }

    /**
     * Total markup for this line item (markup per unit x quantity).
     */
    public function totalMarkup(): float
    {
        return round($this->markupPerUnit() * (int) $this->quantity, 2);
    }
}
