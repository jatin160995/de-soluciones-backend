<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * Default stock quantity given to the auto-generated variant
     * for a product created manually in the admin panel.
     */
    protected const DEFAULT_VARIANT_STOCK = 50;

    protected function afterCreate(): void
    {
        $product = $this->record;

        // Safety net: if the product already has a variant for any reason, don't add another.
        if ($product->variants()->exists()) {
            return;
        }

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $this->generateSku($product->slug),
            'price' => $product->base_price,
            'discounted_price' => $product->discounted_price,
            'stock_quantity' => static::DEFAULT_VARIANT_STOCK,
            'attributes' => null,
            'is_active' => true,
        ]);
    }

    protected function generateSku(string $slug): string
    {
        $base = Str::upper(Str::limit(Str::slug($slug), 60, ''));
        $sku = $base;
        $suffix = 1;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $suffix++;
            $sku = "{$base}-{$suffix}";
        }

        return $sku;
    }
}
