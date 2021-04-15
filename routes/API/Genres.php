<?php

use App\Http\Controllers\GenreController;

Route::prefix('/genres')
    ->name('.genres')
    ->group(function() {
        Route::get('/', [GenreController::class, 'overview'])
            ->name('.overview');

        Route::get('/{genre}', [GenreController::class, 'details'])
            ->name('.details');
    });
