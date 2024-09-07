<?php

use App\Http\Controllers\API\v1\MeController;

Route::prefix('/achievements')
    ->middleware('auth.kurozora')
    ->name('.achievements')
    ->group(function () {
        Route::get('/', [MeController::class, 'getAchievements'])
            ->name('.index');
    });
