<?php

use App\Http\Controllers\API\v1\FavoriteAnimeController;
use App\Http\Controllers\API\v1\MeController;

Route::prefix('/favorite-anime')
    ->name('.favorite-anime')
    ->group(function () {
        Route::get('/', [MeController::class, 'getFavorites'])
            ->middleware('auth.kurozora');

        Route::post('/', [FavoriteAnimeController::class, 'addFavorite'])
            ->middleware('auth.kurozora')
            ->name('.create');
    });
