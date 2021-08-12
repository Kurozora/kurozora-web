<?php

use App\Http\Controllers\GenreController;

Route::prefix('/genres')
    ->name('.genres')
    ->group(function() {
        Route::get('/', [GenreController::class, 'overview'])
            ->middleware('auth.kurozora:optional')
            ->name('.overview');

        Route::prefix('{genre}')
            ->group(function () {
                Route::get('/', [GenreController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');
            });
    });
