<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    public ?string $statusChangeNote = null;

    protected $fillable = [
        'order_number',
        'store_id',
        'user_id',
        'sales_agent_id',
        'address_id',
        'shipping_snapshot',
        'status',
        'subtotal',
        'shipping_cost',

        'discount_percent',
        'discount_amount',
        'total',
        'payment_method',
        'source',
        'customer_name',
        'customer_phone',
        'customer_email',
        'notes',
    ];

    protected $casts = [
        'shipping_snapshot' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->isDirty(['subtotal', 'shipping_cost', 'discount_percent'])) {
                $subtotal = (float) $order->subtotal;
                $discountPercent = (float) $order->discount_percent;

                $order->discount_amount = round($subtotal * ($discountPercent / 100), 2);
                $order->total = round($subtotal - $order->discount_amount + (float) $order->shipping_cost, 2);
            }
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesAgent()
    {
        return $this->belongsTo(User::class, 'sales_agent_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * True for orders entered by hand by a sales agent (phone/WhatsApp sales),
     * as opposed to orders placed through the storefront checkout.
     */
    public function isManual(): bool
    {
        return $this->source === 'manual';
    }
}
