<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
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
         * -------------------------------------------------------------
         * Product images
         * -------------------------------------------------------------
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
         * -------------------------------------------------------------
         * Variant attributes
         * -------------------------------------------------------------
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

                if (
                    ! in_array(
                        (string) $value,
                        $variantAttributes[$key],
                        true
                    )
                ) {
                    $variantAttributes[$key][] =
                        (string) $value;
                }
            }
        }

        /*
         * -------------------------------------------------------------
         * Related products
         * -------------------------------------------------------------
         */
        $relatedProducts = collect();

        if ($product->category_id) {

            $relatedProducts = Product::query()
                ->with([
                    'category',
                    'media',
                ])
                ->where('status', 'active')
                ->where(
                    'category_id',
                    $product->category_id
                )
                ->where(
                    'id',
                    '!=',
                    $product->id
                )
                ->latest()
                ->limit(4)
                ->get();
        }

        /*
         * -------------------------------------------------------------
         * Saved addresses
         * -------------------------------------------------------------
         *
         * Guests receive an empty collection.
         *
         * Logged-in users get their addresses with the default
         * address first.
         */
        $addresses = Auth::check()
            ? Auth::user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get()
            : collect();

        return view('product', compact(
            'product',
            'productImages',
            'variantAttributes',
            'relatedProducts',
            'addresses'
        ));
    }
}
