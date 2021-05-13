<?php

use App\Http\Controllers\AppThemeController;

Route::prefix('/themes')
    ->name('.themes')
    ->group(function() {
        Route::get('/', [AppThemeController::class, 'overview'])
            ->middleware('auth.kurozora:optional')
            ->name('.overview');

        Route::get('/{theme}', [AppThemeController::class, 'details'])
            ->middleware('auth.kurozora:optional')
            ->name('.details');

        Route::get('/{theme}/download', [AppThemeController::class, 'download'])
            ->name('.download');
    });
