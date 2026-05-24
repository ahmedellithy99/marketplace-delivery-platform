<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\StoreController;
use Illuminate\Support\Facades\Route;

// Public routes (no auth required)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::get('/stores/{store:slug}', [StoreController::class, 'show'])->name('stores.show');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Notification routes (all authenticated users)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Customer routes (rate limited)
    Route::middleware(['role:customer', 'throttle:60,1'])
        ->group(base_path('routes/customer.php'));

    // Admin routes (admin + customer_service can access)
    Route::middleware('role:admin,customer_service')
        ->prefix('admin')
        ->group(base_path('routes/admin.php'));

    // Delivery routes
    Route::middleware('role:delivery')
        ->prefix('delivery')
        ->group(base_path('routes/delivery.php'));
});
