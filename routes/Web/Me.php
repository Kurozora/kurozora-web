<?php

use App\Http\Controllers\Web\MeController;

Route::prefix('/me')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [MeController::class, 'index'])
            ->name('me');
    });
