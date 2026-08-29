<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\BuyNowController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/catalogo', [CatalogController::class, 'index'])
    ->name('catalog');

Route::get('/producto/{slug}', [ProductController::class, 'show'])
    ->name('product.show');

Route::get('/ofertas', [OffersController::class, 'index'])
    ->name('offers');


/*
|--------------------------------------------------------------------------
| Carrito
|--------------------------------------------------------------------------
|
| Deliberately NOT behind auth.
|
| Guests can use the cart and continue to checkout without logging in.
|
*/

Route::prefix('carrito')->name('cart.')->group(function () {

    Route::get('/', [CartController::class, 'index'])
        ->name('index');

    Route::get('/resumen', [CartController::class, 'summary'])
        ->name('summary');

    Route::post('/agregar', [CartController::class, 'store'])
        ->name('store');

    Route::patch('/items/{item}', [CartController::class, 'update'])
        ->name('update');

    Route::delete('/items/{item}', [CartController::class, 'destroy'])
        ->name('destroy');

    Route::delete('/', [CartController::class, 'clear'])
        ->name('clear');
});


/*
|--------------------------------------------------------------------------
| Storefront authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/registro', [
        RegisteredUserController::class,
        'create',
    ])->name('register');

    Route::post('/registro', [
        RegisteredUserController::class,
        'store',
    ]);

    Route::get('/login', [
        AuthenticatedSessionController::class,
        'create',
    ])->name('login');

    Route::post('/login', [
        AuthenticatedSessionController::class,
        'store',
    ]);
});


Route::post('/logout', [
    AuthenticatedSessionController::class,
    'destroy',
])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Mi cuenta
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('mi-cuenta')
    ->name('account.')
    ->group(function () {

        Route::get('/', [
            AccountController::class,
            'index',
        ])->name('index');

        Route::put('/perfil', [
            AccountController::class,
            'updateProfile',
        ])->name('profile.update');

        Route::post('/direcciones', [
            AddressController::class,
            'store',
        ])->name('addresses.store');

        Route::put('/direcciones/{address}', [
            AddressController::class,
            'update',
        ])->name('addresses.update');

        Route::delete('/direcciones/{address}', [
            AddressController::class,
            'destroy',
        ])->name('addresses.destroy');
    });


/*
|--------------------------------------------------------------------------
| Normal cart checkout
|--------------------------------------------------------------------------
*/

Route::prefix('checkout')
    ->name('checkout.')
    ->group(function () {

        Route::get('/', [
            CheckoutController::class,
            'index',
        ])->name('index');

        Route::post('/', [
            CheckoutController::class,
            'store',
        ])
            ->middleware('throttle:10,1')
            ->name('store');

        // Route::get('/confirmacion', [
        //     CheckoutController::class,
        //     'confirmation',
        // ])->name('confirmation');
    });


/*
|--------------------------------------------------------------------------
| Comprar ahora
|--------------------------------------------------------------------------
|
| Direct checkout from the product page.
|
| This is intentionally PUBLIC because:
|
| - guests can purchase
| - logged-in users can purchase
|
| The controller determines whether the customer is authenticated.
|
*/
Route::get('/confirmacion', [
    CheckoutController::class,
    'confirmation',
])->name('checkout.confirmation');

Route::post('/comprar-ahora', [
    BuyNowController::class,
    'store',
])
    ->middleware('throttle:10,1')
    ->name('buy-now.store');

Route::post('/checkout/preview-coupon', [CheckoutController::class, 'previewCoupon'])
    ->name('checkout.preview-coupon');
