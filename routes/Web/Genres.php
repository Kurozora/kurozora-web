<?php

use App\Http\Controllers\Web\GenreController;

Route::prefix('/genres')
    ->name('genres')
    ->group(function () {
        Route::get('/', [GenreController::class, 'index'])
            ->name('.index');

        Route::prefix('{genre}')
            ->group(function () {
            Route::get('/', [GenreController::class, 'show'])
                ->name('.details');
        });
    });
