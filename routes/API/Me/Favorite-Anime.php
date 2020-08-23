<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/favorite-anime')
    ->name('favorite-anime.')
    ->group(function() {
        Route::get('/', [MeController::class, 'getFavorites'])
            ->middleware('kurozora.userauth')
            ->name('index');

        Route::post('/', [FavoriteAnimeController::class, 'addFavorite'])
            ->middleware('kurozora.userauth')
            ->name('create');
    });
