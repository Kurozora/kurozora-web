<?php

use App\Http\Controllers\API\v1\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Imdhemy\Purchases\Http\Controllers\ServerNotificationController;

Route::prefix('/store')
    ->name('.store')
    ->group(function () {
        Route::prefix('/subscriptions')
            ->name('.subscriptions')
            ->group(function () {
                Route::post('/apple', function (Request $request) {
                    $request->merge(['provider' => 'app-store']);
                    return app()->call(ServerNotificationController::class);
                })
                    ->name('.apple');
            });

        Route::post('/restore-order', [StoreController::class, 'restoreOrder'])
            ->middleware('auth.kurozora')
            ->name('.restore-order');

        Route::post('/verify', [StoreController::class, 'verifyReceipt'])
            ->middleware('auth.kurozora')
            ->name('.verify');

        Route::get('/transactions', [StoreController::class, 'transactions'])
            ->middleware('auth.kurozora')
            ->name('.transactions');
    });
