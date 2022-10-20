<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('/store')
    ->name('.store')
    ->group(function () {
        Route::prefix('/subscriptions')
            ->name('.subscriptions')
            ->group(function () {
                Route::post('/apple', function(\Illuminate\Http\Request $request) {
                    logger()->channel('stack')->critical(print_r($request->all(), true));
                    Http::post(route('liap.serverNotifications', ['provider' => 'app-store']), $request->all());
                })
                    ->name('.apple');
            });

        Route::post('/verify', [StoreController::class, 'verifyReceipt'])
            ->middleware('auth.kurozora:optional')
            ->name('.verify');
    });
