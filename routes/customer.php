<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
|
| Routes for the Customer role. Protected by auth + role:customer middleware.
|
*/

Route::get('/cart', function () {
    return response('Cart page');
})->name('customer.cart');

Route::get('/orders', function () {
    return response('Customer orders');
})->name('customer.orders');
