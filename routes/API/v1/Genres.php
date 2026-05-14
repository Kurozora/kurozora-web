<?php

use App\Http\Controllers\API\v1\GenreController;

Route::prefix('/genres')
    ->name('.genres')
    ->middleware('cache.headers:private;max_age=3600;etag')
    ->group(function () {
        Route::get('/', [GenreController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{genre}')
            ->group(function () {
                Route::get('/', [GenreController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');
            });
    });
