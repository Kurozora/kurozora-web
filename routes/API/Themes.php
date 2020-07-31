<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/themes')
    ->name('themes.')
    ->group(function() {
        Route::get('/', [AppThemeController::class, 'overview']);

        Route::get('/{theme}', [AppThemeController::class, 'details'])
            ->name('details');

        Route::get('/{theme}/download', [AppThemeController::class, 'download'])
            ->name('download');
    });
