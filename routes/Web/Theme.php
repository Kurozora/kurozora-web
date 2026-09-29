<?php

use App\Http\Controllers\Web\ThemeController;

Route::prefix('/themes')
    ->name('themes')
    ->group(function () {
        Route::get('/', [ThemeController::class, 'index'])
            ->name('.index');

        Route::prefix('{theme}')
            ->group(function () {
                Route::get('/', [ThemeController::class, 'show'])
                    ->name('.details');
            });
    });
