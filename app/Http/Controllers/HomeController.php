<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;
use App\Models\HeroBanner;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->where('type', 'product')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $featuredProducts = Product::query()
            ->with(['category', 'media'])
            // Lets each card decide "Agregar" vs "Ver opciones" without an N+1.
            ->withCount('variants')
            ->where('status', 'active')
            ->where('is_featured', true)
            ->latest()
            ->limit(8)
            ->get();

        // Heuristic "deal of the day": biggest discount % among active,
        // currently-discounted products. No manual flag exists yet —
        // ask Daniel if he wants to pick this one manually in the admin.
        $dealOfTheDay = Product::query()
            ->where('status', 'active')
            ->whereNotNull('discounted_price')
            ->get()
            ->sortByDesc(fn(Product $p) => 1 - ($p->discounted_price / $p->base_price))
            ->first();

        $heroBanners = HeroBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('categories', 'featuredProducts', 'dealOfTheDay', 'heroBanners'));
    }
}
