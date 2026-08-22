<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OffersController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['category', 'media'])
            // Lets each card decide "Agregar" vs "Ver opciones" without an N+1.
            ->withCount('variants')
            ->where('status', 'active')
            ->whereNotNull('discounted_price')
            ->whereColumn('discounted_price', '<', 'base_price')
            ->where('base_price', '>', 0);

        /*
         * Category filter
         *
         * URL example:
         * /ofertas?categorias[]=electronics
         */
        $selectedCategories = $request->input('categorias', []);

        if (!is_array($selectedCategories)) {
            $selectedCategories = [$selectedCategories];
        }

        $selectedCategories = array_values(
            array_filter($selectedCategories)
        );

        if (!empty($selectedCategories)) {
            $query->whereHas('category', function ($categoryQuery) use ($selectedCategories) {
                $categoryQuery->whereIn('slug', $selectedCategories);
            });
        }

        /*
         * Price filter
         */
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        if ($minPrice !== null && $minPrice !== '') {
            $query->where('discounted_price', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('discounted_price', '<=', (float) $maxPrice);
        }

        /*
         * Minimum discount filter
         */
        $minDiscount = $request->input('min_discount');

        if (in_array((string) $minDiscount, ['10', '20', '30'], true)) {
            $query->whereRaw(
                '((base_price - discounted_price) / base_price) * 100 >= ?',
                [(float) $minDiscount]
            );
        }

        /*
         * Sorting
         */
        $sort = $request->input('sort', 'discount');

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('discounted_price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('discounted_price', 'desc');
                break;

            case 'latest':
                $query->latest();
                break;

            case 'discount':
            default:
                $query->orderByRaw(
                    '((base_price - discounted_price) / base_price) DESC'
                );
                break;
        }

        /*
         * 9 products per page to match the HTML prototype.
         */
        $offers = $query
            ->paginate(9)
            ->withQueryString();

        /*
         * Categories which actually contain discounted products.
         *
         * We deliberately do not use a hard-coded category list.
         */
        $categories = Category::query()
            ->where('type', 'product')
            ->where('is_active', true)
            ->whereHas('products', function ($productQuery) {
                $productQuery
                    ->where('status', 'active')
                    ->whereNotNull('discounted_price')
                    ->whereColumn('discounted_price', '<', 'base_price')
                    ->where('base_price', '>', 0);
            })
            ->withCount([
                'products as offers_count' => function ($productQuery) {
                    $productQuery
                        ->where('status', 'active')
                        ->whereNotNull('discounted_price')
                        ->whereColumn('discounted_price', '<', 'base_price')
                        ->where('base_price', '>', 0);
                },
            ])
            ->orderBy('name')
            ->get();

        /*
         * Calculate the highest real discount currently available.
         * This drives the "Hasta XX%" text in the banner.
         */
        $maxDiscount = Product::query()
            ->where('status', 'active')
            ->whereNotNull('discounted_price')
            ->whereColumn('discounted_price', '<', 'base_price')
            ->where('base_price', '>', 0)
            ->selectRaw(
                'MAX(((base_price - discounted_price) / base_price) * 100) as max_discount'
            )
            ->value('max_discount');

        $maxDiscount = $maxDiscount !== null
            ? (int) round($maxDiscount)
            : 0;

        /*
         * Total number of active offers, before current filters.
         */
        $totalOffers = Product::query()
            ->where('status', 'active')
            ->whereNotNull('discounted_price')
            ->whereColumn('discounted_price', '<', 'base_price')
            ->where('base_price', '>', 0)
            ->count();

        return view('ofertas', [
            'offers' => $offers,
            'categories' => $categories,
            'selectedCategories' => $selectedCategories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'minDiscount' => $minDiscount,
            'sort' => $sort,
            'maxDiscount' => $maxDiscount,
            'totalOffers' => $totalOffers,
        ]);
    }
}
