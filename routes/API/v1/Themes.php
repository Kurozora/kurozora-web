<?php

use App\Http\Controllers\ThemeController;

Route::prefix('/themes')
    ->name('.themes')
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
