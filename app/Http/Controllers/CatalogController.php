<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        /*
         * Active top-level product categories.
         *
         * We use withCount() so the sidebar can show:
         *
         * Herramientas (24)
         * Tecnología (18)
         * etc.
         */
        $categories = Category::query()
            ->where('type', 'product')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount([
                'products' => fn($query) => $query->where('status', 'active'),
            ])
            ->orderBy('sort_order')
            ->get();


        /*
         * Product query.
         */
        $productsQuery = Product::query()
            ->with([
                'category',
                'media',
            ])
            ->where('status', 'active');


        /*
         * ========================================
         * Search filter
         * ========================================
         *
         * The header search bar submits ?q=... to the
         * catalog, alongside any active filters/sort.
         *
         * We match against the product name or
         * description. LIKE wildcards in the user's term
         * are escaped so "%" and "_" are treated as
         * literal characters rather than wildcards.
         */
        $searchTerm = null;

        if ($request->filled('q')) {

            $searchTerm = $request->string('q')->trim()->toString();

            $escaped = addcslashes($searchTerm, '%_\\');

            $productsQuery->where(function ($query) use ($escaped) {
                $query
                    ->where('name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%");
            });
        }


        /*
         * ========================================
         * Category filter
         * ========================================
         *
         * /catalogo?categoria=herramientas
         */
        if ($request->filled('categoria')) {

            $categorySlug = $request->string('categoria')->toString();

            $productsQuery->whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            });
        }


        /*
         * ========================================
         * Price filter
         * ========================================
         */

        if ($request->filled('min')) {

            $minPrice = (float) $request->input('min');

            $productsQuery->whereRaw(
                'COALESCE(discounted_price, base_price) >= ?',
                [$minPrice]
            );
        }


        if ($request->filled('max')) {

            $maxPrice = (float) $request->input('max');

            $productsQuery->whereRaw(
                'COALESCE(discounted_price, base_price) <= ?',
                [$maxPrice]
            );
        }


        /*
         * ========================================
         * Stock filter
         * ========================================
         *
         * Product stock lives on product_variants.
         *
         * "Solo en existencia" means at least one
         * active variant has stock > 0.
         */
        if ($request->boolean('stock')) {

            $productsQuery->whereHas('variants', function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('stock_quantity', '>', 0);
            });
        }


        /*
         * ========================================
         * Offers filter
         * ========================================
         */

        if ($request->boolean('ofertas')) {

            $productsQuery
                ->whereNotNull('discounted_price')
                ->whereColumn('discounted_price', '<', 'base_price');
        }


        /*
         * ========================================
         * Sorting
         * ========================================
         */

        $sort = $request->input('sort', 'relevant');

        switch ($sort) {

            case 'price_asc':

                $productsQuery->orderByRaw(
                    'COALESCE(discounted_price, base_price) ASC'
                );

                break;


            case 'price_desc':

                $productsQuery->orderByRaw(
                    'COALESCE(discounted_price, base_price) DESC'
                );

                break;


            case 'newest':

                $productsQuery->latest('created_at');

                break;


            /*
             * We don't currently have a reviews/ratings
             * system in the backend.
             *
             * Therefore "Mejor calificados" is not
             * implemented yet.
             *
             * Keep the option hidden from the frontend
             * until reviews exist.
             */
            case 'relevant':
            default:

                $productsQuery
                    ->orderByDesc('is_featured')
                    ->latest('created_at');

                break;
        }


        /*
         * ========================================
         * Pagination
         * ========================================
         *
         * 12 products per page, matching the HTML
         * design.
         */
        $products = $productsQuery
            ->paginate(12)
            ->withQueryString();


        /*
         * Currently selected category.
         *
         * Used to highlight the sidebar checkbox.
         */
        $selectedCategory = null;

        if ($request->filled('categoria')) {

            $selectedCategory = $categories->firstWhere(
                'slug',
                $request->input('categoria')
            );

            /*
             * If category is a child category rather
             * than a top-level category, find it too.
             */
            if (! $selectedCategory) {

                $selectedCategory = Category::query()
                    ->where('type', 'product')
                    ->where('is_active', true)
                    ->where('slug', $request->input('categoria'))
                    ->first();
            }
        }


        return view('catalog', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'searchTerm' => $searchTerm,
            'filters' => [
                'min' => $request->input('min'),
                'max' => $request->input('max'),
                'stock' => $request->boolean('stock'),
                'ofertas' => $request->boolean('ofertas'),
                'sort' => $sort,
            ],
        ]);
    }
}
