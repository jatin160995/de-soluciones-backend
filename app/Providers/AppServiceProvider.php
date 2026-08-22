<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\CartService;
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
        /*
         * One cart per request. The controller and the storefront view composer
         * both need it, and CartService memoises the resolved cart plus any
         * cart_token cookie it queued — a queued cookie is invisible to
         * request()->cookie(), so a second instance would mint a second token
         * and leave an orphan carts row behind.
         */
        $this->app->scoped(CartService::class);
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
