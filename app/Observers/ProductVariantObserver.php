<?php

namespace App\Observers;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    public function created(ProductVariant $variant): void
    {
        if ($variant->stock_quantity > 0) {
            InventoryMovement::create([
                'variant_id' => $variant->id,
                'type' => 'in',
                'quantity' => $variant->stock_quantity,
                'reason' => 'Initial stock on variant creation',
                'reference_type' => ProductVariant::class,
                'reference_id' => $variant->id,
                'created_by' => auth()->id(),
            ]);
        }
    }

    public function updating(ProductVariant $variant): void
    {
        if (! $variant->isDirty('stock_quantity')) {
            return;
        }

        $diff = $variant->stock_quantity - $variant->getOriginal('stock_quantity');

        if ($diff === 0) {
            return;
        }

        InventoryMovement::create([
            'variant_id' => $variant->id,
            'type' => $diff > 0 ? 'in' : 'out',
            'quantity' => abs($diff),
            'reason' => 'Manual adjustment via admin panel',
            'reference_type' => ProductVariant::class,
            'reference_id' => $variant->id,
            'created_by' => auth()->id(),
        ]);
    }
}