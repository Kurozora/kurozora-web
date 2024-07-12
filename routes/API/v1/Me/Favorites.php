<?php

use App\Http\Controllers\API\v1\MeController;
use App\Http\Controllers\API\v1\UserFavoriteController;

Route::prefix('/favorites')
    ->name('.favorites')
    ->group(function () {
        Route::get('/', [MeController::class, 'getFavorites'])
            ->name('.index');

        Route::post('/', [UserFavoriteController::class, 'create'])
            ->name('.create');
    });
