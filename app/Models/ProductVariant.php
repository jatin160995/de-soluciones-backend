<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
   protected $fillable = [
    'product_id', 'sku', 'price', 'discounted_price', 'stock_quantity',
    'attributes', 'size', 'color', 'extra_attributes', 'is_active',
];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    protected $appends = ['size', 'color', 'extra_attributes'];

    public function getEffectivePriceAttribute(): string
    {
        return $this->discounted_price ?? $this->price;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }

    // Virtual field: reads/writes attributes['size']
   protected function size(): Attribute
{
    return Attribute::make(
        get: fn () => $this->currentAttributes()['size'] ?? null,
        set: fn ($value) => ['attributes' => json_encode($this->mergeIntoAttributes('size', $value))],
    );
}

    // Virtual field: reads/writes attributes['color']
    protected function color(): Attribute
{
    return Attribute::make(
        get: fn () => $this->currentAttributes()['color'] ?? null,
        set: fn ($value) => ['attributes' => json_encode($this->mergeIntoAttributes('color', $value))],
    );
}

    // Virtual field: everything in `attributes` EXCEPT size/color —
    // lets the Filament form use a free-form KeyValue field for
    // material, weight, etc. without disturbing the indexed keys.
    protected function extraAttributes(): Attribute
{
    return Attribute::make(
        get: function () {
            $attrs = $this->currentAttributes();
            unset($attrs['size'], $attrs['color']);
            return $attrs;
        },
        set: function ($value) {
            $attrs = $this->currentAttributes();
            $preserved = array_filter([
                'size' => $attrs['size'] ?? null,
                'color' => $attrs['color'] ?? null,
            ], fn ($v) => $v !== null);

            return ['attributes' => json_encode(array_merge((array) $value, $preserved))];
        },
    );
}

    protected function mergeIntoAttributes(string $key, $value): array
{
    $attrs = $this->currentAttributes();

    if ($value === null || $value === '') {
        unset($attrs[$key]);
    } else {
        $attrs[$key] = $value;
    }

    return $attrs;
}
protected function currentAttributes(): array
{
    $raw = $this->attributes['attributes'] ?? null;

    if (is_array($raw)) {
        return $raw;
    }

    if (is_string($raw) && $raw !== '') {
        return json_decode($raw, true) ?? [];
    }

    return [];
}
}