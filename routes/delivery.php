<?php

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

Route::get('/assignments', function () {
    return response('Delivery assignments');
})->name('delivery.assignments.index');
