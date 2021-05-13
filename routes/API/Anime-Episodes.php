<?php

use App\Http\Controllers\AnimeEpisodeController;

Route::prefix('/anime-episodes')
    ->name('.episodes')
    ->group(function() {
        Route::get('/{episode}', [AnimeEpisodeController::class, 'details'])
            ->middleware('auth.kurozora:optional')
            ->name('.details');

        Route::post('/{episode}/watched', [AnimeEpisodeController::class, 'watched'])
            ->middleware('auth.kurozora');
    });
