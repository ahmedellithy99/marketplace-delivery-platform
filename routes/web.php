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
    Route::middleware('role:customer')
        ->group(base_path('routes/customer.php'));

    // Admin routes
    Route::middleware('role:admin')
        ->prefix('admin')
        ->group(base_path('routes/admin.php'));

    // Super Admin routes
    Route::middleware('role:super_admin')
        ->prefix('super-admin')
        ->group(base_path('routes/super_admin.php'));

    // Delivery routes
    Route::middleware('role:delivery')
        ->prefix('delivery')
        ->group(base_path('routes/delivery.php'));
});
