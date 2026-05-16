<?php

use App\Http\Controllers\Customer\CartController;
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
Route::resource('cart', CartController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->names('customer.cart')
    ->parameter('cart', 'cartItem');

Route::delete('/cart/clear', [CartController::class, 'clear'])->name('customer.cart.clear');

// Orders
Route::get('/orders', function () {
    return response('Customer orders');
})->name('customer.orders');
