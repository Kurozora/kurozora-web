<?php

use App\Http\Controllers\API\v1\LanguageController;

Route::prefix('/languages')
    ->name('.languages')
    ->group(function () {
        Route::get('/', [LanguageController::class, 'index'])
            ->name('.index');

        Route::prefix('{language}')
            ->group(function () {
                Route::get('/', [LanguageController::class, 'details'])
                    ->name('.details');
            });
    });
