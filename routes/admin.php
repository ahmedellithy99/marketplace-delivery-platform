<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for the Admin role. These are prefixed with /admin and protected
| by auth + role:admin middleware.
|
*/

Route::get('/dashboard', function () {
    return response('Admin dashboard');
})->name('admin.dashboard');

Route::get('/stores', function () {
    return response('Admin stores');
})->name('admin.stores.index');

Route::resource('categories', CategoryController::class)
    ->names('admin.categories')
    ->except(['show']);

Route::resource('products', ProductController::class)
    ->names('admin.products')
    ->except(['show']);

Route::patch('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])
    ->name('admin.products.toggle-availability');

Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])
    ->name('admin.products.variants.store');

Route::put('/products/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])
    ->name('admin.products.variants.update');

Route::delete('/products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])
    ->name('admin.products.variants.destroy');

Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
Route::post('/orders/{order}/accept', [OrderController::class, 'accept'])->name('admin.orders.accept');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
Route::post('/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('admin.orders.assign-delivery');

Route::get('/deliveries', function () {
    return response('Admin deliveries');
})->name('admin.deliveries.index');
