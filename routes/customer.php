<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
|
| Routes for the Customer role. Protected by auth + role:customer middleware.
|
*/

// Cart routes
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('customer.cart.clear');

Route::resource('cart', CartController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->names('customer.cart')
    ->parameter('cart', 'cartItem');

// Order routes
Route::resource('orders', OrderController::class)
    ->only(['index', 'store', 'show'])
    ->names('customer.orders');
