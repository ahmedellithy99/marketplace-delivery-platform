<?php

use App\Http\Controllers\Delivery\DeliveryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Delivery Routes
|--------------------------------------------------------------------------
|
| Routes for the Delivery Man role. These are prefixed with /delivery
| and protected by auth + role:delivery middleware.
|
*/

Route::get('/assignments', [DeliveryController::class, 'index'])
    ->name('delivery.assignments.index');

Route::get('/assignments/{delivery}', [DeliveryController::class, 'show'])
    ->name('delivery.assignments.show');

Route::post('/assignments/{delivery}/preparing', [DeliveryController::class, 'markPreparing'])
    ->name('delivery.assignments.preparing');

Route::post('/assignments/{delivery}/picked-up', [DeliveryController::class, 'markPickedUp'])
    ->name('delivery.assignments.picked-up');

Route::post('/assignments/{delivery}/delivered', [DeliveryController::class, 'markDelivered'])
    ->name('delivery.assignments.delivered');
