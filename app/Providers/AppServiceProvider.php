<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ProductVariantObserver;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Observers\OrderObserver;
use App\Models\Order;

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
        ]);
        Order::observe(OrderObserver::class);
    }
}
