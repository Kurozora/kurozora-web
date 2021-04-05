<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/store')
    ->name('.store')
    ->group(function() {
        Route::get('/', [StoreController::class, 'index']);

        Route::get('/{productID}', [StoreController::class, 'details'])
            ->name('.details');

        Route::post('/verify', [StoreController::class, 'verifyReceipt'])
            ->middleware('kurozora.userauth:optional');
    });
