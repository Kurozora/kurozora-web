<?php

use App\Http\Controllers\API\v1\MeSettingsController;

Route::prefix('/settings')
    ->middleware('auth.kurozora')
    ->name('.settings')
    ->group(function () {
        Route::get('/', [MeSettingsController::class, 'show']);

        Route::post('/', [MeSettingsController::class, 'update'])
            ->middleware('user.not-timed-out')
            ->name('.update');
    });
