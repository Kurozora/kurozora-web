<?php

use App\Http\Controllers\API\v1\LiteratureCastController;
use App\Http\Controllers\API\v1\ShowCastController;

Route::prefix('/cast')
    ->name('.cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [ShowCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/show-cast')
    ->name('.show-cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [ShowCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/literature-cast')
    ->name('.literature-cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [LiteratureCastController::class, 'details'])
                    ->name('.details');
            });
    });
