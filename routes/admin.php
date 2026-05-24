<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DeliveryManController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\StoreTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes accessible by: super_admin, admin, customer_service
| Further restricted by role within route groups.
|
*/

// ─── Shared Routes (all admin roles) ──────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// ─── Order Management (admin + customer_service) ──────────────────────
Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
Route::post('/orders/{order}/accept', [OrderController::class, 'accept'])->name('admin.orders.accept');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
Route::post('/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('admin.orders.assign-delivery');
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

// ─── Delivery Tracking (admin + customer_service) ─────────────────────
Route::get('/deliveries', [DeliveryController::class, 'index'])->name('admin.deliveries.index');
Route::get('/deliveries/men/{user}', [DeliveryController::class, 'show'])->name('admin.deliveries.show');

// ─── Admin-Only Routes (not customer_service) ─────────────────────────
Route::middleware('role:admin')->group(function () {

    // Store Types (use ID binding for admin CRUD)
    Route::prefix('store-types')->group(function () {
        Route::get('/', [StoreTypeController::class, 'index'])->name('admin.store-types.index');
        Route::get('/create', [StoreTypeController::class, 'create'])->name('admin.store-types.create');
        Route::post('/', [StoreTypeController::class, 'store'])->name('admin.store-types.store');
        Route::get('/{store_type}/edit', [StoreTypeController::class, 'edit'])->name('admin.store-types.edit');
        Route::put('/{store_type}', [StoreTypeController::class, 'update'])->name('admin.store-types.update');
        Route::delete('/{store_type}', [StoreTypeController::class, 'destroy'])->name('admin.store-types.destroy');
        Route::patch('/{store_type}/toggle-active', [StoreTypeController::class, 'toggleActive'])->name('admin.store-types.toggle-active');
    });

    // Stores
    Route::resource('stores', StoreController::class)
        ->names('admin.stores')
        ->except(['show']);
    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('admin.stores.show');

    // Categories
    Route::resource('categories', CategoryController::class)
        ->names('admin.categories')
        ->except(['show']);

    // Products
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

    Route::post('/products/{product}/discounts', [ProductController::class, 'storeDiscount'])
        ->name('admin.products.discounts.store');

    Route::delete('/products/{product}/discounts/{discount}', [ProductController::class, 'destroyDiscount'])
        ->name('admin.products.discounts.destroy');

    // Discounts
    Route::resource('discounts', DiscountController::class)
        ->names('admin.discounts')
        ->except(['show']);

    Route::patch('/discounts/{discount}/toggle-active', [DiscountController::class, 'toggleActive'])
        ->name('admin.discounts.toggle-active');

    Route::get('/discounts-targets', [DiscountController::class, 'targets'])
        ->name('admin.discounts.targets');

    // Delivery Men Management
    Route::resource('delivery-men', DeliveryManController::class)
        ->names('admin.delivery-men')
        ->except(['show']);
});

// ─── Super Admin Only (staff management) ──────────────────────────────
Route::middleware('role:super_admin')->group(function () {
    Route::resource('staff', StaffController::class)
        ->names('admin.staff')
        ->except(['show']);

    Route::get('/change-password', [StaffController::class, 'changePassword'])->name('admin.change-password');
    Route::put('/change-password', [StaffController::class, 'updatePassword'])->name('admin.update-password');
});
