<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Customer routes
    Route::middleware('role:customer')->group(function () {
        Route::get('/cart', function () {
            return response('Cart page');
        })->name('customer.cart');

        Route::get('/orders', function () {
            return response('Customer orders');
        })->name('customer.orders');
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return response('Admin dashboard');
        })->name('admin.dashboard');

        Route::get('/stores', function () {
            return response('Admin stores');
        })->name('admin.stores.index');

        Route::get('/categories', function () {
            return response('Admin categories');
        })->name('admin.categories.index');

        Route::get('/products', function () {
            return response('Admin products');
        })->name('admin.products.index');

        Route::get('/orders', function () {
            return response('Admin orders');
        })->name('admin.orders.index');

        Route::get('/deliveries', function () {
            return response('Admin deliveries');
        })->name('admin.deliveries.index');
    });

    // Super Admin routes
    Route::middleware('role:super_admin')->prefix('super-admin')->group(function () {
        Route::get('/stores', function () {
            return response('Super admin stores');
        })->name('super_admin.stores.index');

        Route::get('/stores/create', function () {
            return response('Super admin create store');
        })->name('super_admin.stores.create');

        Route::get('/store-types', function () {
            return response('Super admin store types');
        })->name('super_admin.store_types.index');
    });

    // Delivery routes
    Route::middleware('role:delivery')->prefix('delivery')->group(function () {
        Route::get('/assignments', function () {
            return response('Delivery assignments');
        })->name('delivery.assignments.index');
    });
});
