<?php

use App\Http\Controllers\API\v1\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('/store')
    ->name('.store')
    ->group(function () {
        Route::prefix('/subscriptions')
            ->name('.subscriptions')
            ->group(function () {
                Route::post('/apple', function(\Illuminate\Http\Request $request) {
                    Http::post(route('liap.serverNotifications', ['provider' => 'app-store']), $request->all());
                })
                    ->name('.apple');
            });

        Route::post('/verify', [StoreController::class, 'verifyReceipt'])
            ->middleware('auth.kurozora:optional')
            ->name('.verify');
    });
