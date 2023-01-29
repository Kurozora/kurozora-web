<?php

use App\Http\Controllers\API\v1\MeController;
use App\Http\Controllers\API\v1\UserFavoriteController;

Route::prefix('/favorites')
    ->name('.favorites')
    ->group(function () {
        Route::get('/', [MeController::class, 'getFavorites'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::post('/', [UserFavoriteController::class, 'create'])
            ->middleware('auth.kurozora')
            ->name('.create');
    });
