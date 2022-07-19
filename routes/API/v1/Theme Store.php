<?php

use App\Http\Controllers\AppThemeController;

Route::prefix('/theme-store')
    ->name('.theme-store')
    ->group(function () {
        Route::get('/', [AppThemeController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{appTheme}')
            ->group(function () {
                Route::get('/', [AppThemeController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/download', [AppThemeController::class, 'download'])
                    ->name('.download');
            });
    });
