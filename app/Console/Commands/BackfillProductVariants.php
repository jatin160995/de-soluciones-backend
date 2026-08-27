<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillProductVariants extends Command
{
    protected $signature = 'products:backfill-variants
        {--stock=10 : Stock quantity to set on each generated variant}';

    protected $description = 'Creates a default ProductVariant for every product that does not have one yet (fixes products imported via CSV/SQL that skipped variant creation).';

    public function handle(): int
    {
        $defaultStock = (int) $this->option('stock');

        $products = Product::doesntHave('variants')->get();

        if ($products->isEmpty()) {
            $this->info('Every product already has at least one variant. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info("Found {$products->count()} product(s) without a variant. Creating defaults...");
        $bar = $this->output->createProgressBar($products->count());

        foreach ($products as $product) {
            DB::transaction(function () use ($product, $defaultStock) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $this->generateSku($product),
                    'price' => $product->base_price,
                    'discounted_price' => $product->discounted_price,
                    'stock_quantity' => $defaultStock,
                    'attributes' => null,
                    'is_active' => true,
                ]);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Done. Every product now has at least one sellable variant.');

        return self::SUCCESS;
    }

    protected function generateSku(Product $product): string
    {
        $base = Str::upper(Str::limit(Str::slug($product->slug), 60, ''));
        $sku = $base;
        $suffix = 1;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $suffix++;
            $sku = "{$base}-{$suffix}";
        }

        return $sku;
    }
}
