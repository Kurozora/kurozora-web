<?php

use App\Http\Controllers\API\v1\ThemeController;

Route::prefix('/themes')
    ->name('.themes')
    ->middleware('cache.headers:private;max_age=3600;etag')
    ->group(function () {
        Route::get('/', [ThemeController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{theme}')
            ->group(function () {
                Route::get('/', [ThemeController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');
            });
    });
