<?php

use App\Http\Controllers\Admin\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
|
| Routes for the Super Admin role. These are prefixed with /super-admin
| and protected by auth + role:super_admin middleware.
|
*/

Route::resource('stores', StoreController::class)
    ->names([
        'index' => 'super_admin.stores.index',
        'create' => 'super_admin.stores.create',
        'store' => 'super_admin.stores.store',
        'edit' => 'super_admin.stores.edit',
        'update' => 'super_admin.stores.update',
        'destroy' => 'super_admin.stores.destroy',
    ]);

Route::get('/store-types', function () {
    return response('Super admin store types');
})->name('super_admin.store_types.index');
