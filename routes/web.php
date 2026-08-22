<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

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
| Deliberately NOT behind `auth`: this store sells pago contra entrega and
| guest checkout is the primary path. A guest is identified by the
| `cart_token` cookie (see CartService); on login/register their cart is
| merged into the customer's own.
|
| Everything except index() answers in JSON for the fetch calls in
| public/storefront/js/script.js.
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
|
| Customer-facing login / signup / logout on the shared `web` guard.
| Admin isolation is enforced by User::canAccessPanel() (customers are
| denied /admin) rather than by a separate guard.
*/

Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Mi cuenta
|--------------------------------------------------------------------------
|
| Customer account area: order history, saved addresses and personal
| details. Guests hitting these routes are sent to /login and returned
| here afterwards by the login controller's redirect()->intended().
*/

Route::middleware('auth')
    ->prefix('mi-cuenta')
    ->name('account.')
    ->group(function () {
        Route::get('/', [AccountController::class, 'index'])
            ->name('index');

        Route::put('/perfil', [AccountController::class, 'updateProfile'])
            ->name('profile.update');

        Route::post('/direcciones', [AddressController::class, 'store'])
            ->name('addresses.store');

        Route::put('/direcciones/{address}', [AddressController::class, 'update'])
            ->name('addresses.update');

        Route::delete('/direcciones/{address}', [AddressController::class, 'destroy'])
            ->name('addresses.destroy');
    });
