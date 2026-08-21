<?php

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
