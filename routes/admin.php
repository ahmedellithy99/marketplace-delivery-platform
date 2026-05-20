<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StoreController;
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

Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

Route::resource('stores', StoreController::class)
    ->names('admin.stores')
    ->except(['show']);

Route::get('/stores/{store}', [StoreController::class, 'show'])->name('admin.stores.show');

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

Route::patch('/products/{product}/variants/{variant}/set-default', [ProductController::class, 'setDefaultVariant'])
    ->name('admin.products.variants.set-default');

Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
Route::post('/orders/{order}/accept', [OrderController::class, 'accept'])->name('admin.orders.accept');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
Route::post('/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('admin.orders.assign-delivery');

Route::get('/deliveries', [DeliveryController::class, 'index'])->name('admin.deliveries.index');

Route::resource('discounts', DiscountController::class)
    ->names('admin.discounts')
    ->except(['show']);

Route::patch('/discounts/{discount}/toggle-active', [DiscountController::class, 'toggleActive'])
    ->name('admin.discounts.toggle-active');

Route::get('/discounts-targets', [DiscountController::class, 'targets'])
    ->name('admin.discounts.targets');

Route::post('/products/{product}/discounts', [ProductController::class, 'storeDiscount'])
    ->name('admin.products.discounts.store');

Route::delete('/products/{product}/discounts/{discount}', [ProductController::class, 'destroyDiscount'])
    ->name('admin.products.discounts.destroy');
