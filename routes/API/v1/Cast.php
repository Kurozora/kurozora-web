<?php

use App\Http\Controllers\API\v1\AnimeCastController;
use App\Http\Controllers\API\v1\MangaCastController;

Route::prefix('/cast')
    ->name('.cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [AnimeCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/anime-cast')
    ->name('.anime-cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [AnimeCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/manga-cast')
    ->name('.manga-cast')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [MangaCastController::class, 'details'])
                    ->name('.details');
            });
    });
