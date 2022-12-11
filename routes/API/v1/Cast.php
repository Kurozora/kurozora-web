<?php

use App\Http\Controllers\API\v1\AnimeCastController;

Route::prefix('/cast')
    ->name('.cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [AnimeCastController::class, 'details'])
                    ->name('.details');
            });
    });
