<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::query()
            ->with([
                'category',
                'variants' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderBy('id'),
                'media',
            ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        /*
         * Product images from Spatie Media Library.
         *
         * We intentionally use the original media URL here rather than
         * the thumb conversion because the product detail page needs
         * larger images.
         */
        $productImages = $product
            ->getMedia('images')
            ->map(fn($media) => [
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'alt' => $media->getCustomProperty('alt_text')
                    ?: $media->name
                    ?: $product->name,
            ])
            ->values();

        /*
         * Build normal text-based variant options.
         *
         * Example:
         *
         * Color: Mid night, Black, Red
         * Size: 13", 15"
         *
         * Any other attributes stored in the JSON are also collected.
         */
        $variantAttributes = [];

        foreach ($product->variants as $variant) {
            $attributes = $variant->attributes ?? [];

            if (! is_array($attributes)) {
                continue;
            }

            foreach ($attributes as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $variantAttributes[$key] ??= [];

                if (! in_array((string) $value, $variantAttributes[$key], true)) {
                    $variantAttributes[$key][] = (string) $value;
                }
            }
        }

        /*
         * Related products from the same category.
         *
         * If this product currently has no category, we simply don't
         * show the related-products section.
         */
        $relatedProducts = collect();

        if ($product->category_id) {
            $relatedProducts = Product::query()
                ->with(['category', 'media'])
                ->where('status', 'active')
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->latest()
                ->limit(4)
                ->get();
        }

        return view('product', compact(
            'product',
            'productImages',
            'variantAttributes',
            'relatedProducts'
        ));
    }
}
