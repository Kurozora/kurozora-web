<?php

use App\Http\Controllers\AnimeCastController;

Route::prefix('/cast')
    ->name('.cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [AnimeCastController::class, 'details'])
                    ->name('.details');
            });
    });
