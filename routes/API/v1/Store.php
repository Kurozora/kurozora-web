<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;
use Imdhemy\Purchases\Http\Controllers\ServerNotificationController;

Route::prefix('/store')
    ->name('.store')
    ->group(function () {
        Route::prefix('/subscriptions')
            ->name('.subscriptions')
            ->group(function () {
                Route::post('/google', ServerNotificationController::class)
                    ->name('.google');

                Route::post('/apple', ServerNotificationController::class)
                    ->name('.apple');
            });

        Route::post('/verify', [StoreController::class, 'verifyReceipt'])
            ->middleware('auth.kurozora:optional')
            ->name('.verify');
    });
