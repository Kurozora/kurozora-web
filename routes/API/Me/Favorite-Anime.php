<?php

use App\Http\Controllers\FavoriteAnimeController;
use App\Http\Controllers\MeController;

Route::prefix('/favorite-anime')
    ->name('.favorite-anime')
    ->group(function() {
        Route::get('/', [MeController::class, 'getFavorites'])
            ->middleware('auth.kurozora');

        Route::post('/', [FavoriteAnimeController::class, 'addFavorite'])
            ->middleware('auth.kurozora')
            ->name('.create');
    });
