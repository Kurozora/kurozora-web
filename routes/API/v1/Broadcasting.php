<?php

use App\Http\Controllers\API\v1\BroadcastingController;

Route::prefix('/broadcasting')
    ->name('.broadcasting')
    ->group(function () {
        Route::post('/auth', [BroadcastingController::class, 'auth'])
            ->middleware('auth.kurozora')
            ->name('.auth');
    });
