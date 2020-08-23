<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime-episodes')
    ->name('episodes.')
    ->group(function() {
        Route::get('/{episode}', [AnimeEpisodeController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('details');

        Route::post('/{episode}/watched', [AnimeEpisodeController::class, 'watched'])
            ->middleware('kurozora.userauth');
    });
