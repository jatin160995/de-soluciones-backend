<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\StorefrontComposer;
use App\Observers\ProductVariantObserver;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Observers\OrderObserver;
use App\Models\Order;
use App\Models\HeroBanner;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductVariant::observe(ProductVariantObserver::class);
        Relation::enforceMorphMap([
            'product'  => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
            'store'    => \App\Models\Store::class,
            'user'     => \App\Models\User::class,
            'hero_banner'     => \App\Models\HeroBanner::class,
        ]);
        Order::observe(OrderObserver::class);

        // Share website-level header/footer settings with the storefront layout.
        View::composer('layouts.storefront', StorefrontComposer::class);
    }
}
