<?php

use App\Http\Controllers\StoreController;

Route::prefix('/store')
    ->name('.store')
    ->group(function() {
        Route::get('/', [StoreController::class, 'index']);

        Route::get('/{productID}', [StoreController::class, 'details'])
            ->name('.details');

        Route::post('/verify', [StoreController::class, 'verifyReceipt'])
            ->middleware('auth.kurozora:optional')
            ->name('.verify');
    });
