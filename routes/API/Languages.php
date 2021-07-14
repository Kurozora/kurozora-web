<?php

use App\Http\Controllers\LanguageController;

Route::prefix('/languages')
    ->name('.languages')
    ->group(function() {
        Route::get('/', [LanguageController::class, 'overview'])
            ->name('.overview');

        Route::prefix('{language}')
            ->group(function () {
                Route::get('/', [LanguageController::class, 'details'])
                    ->name('.details');
            });
    });
