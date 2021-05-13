<?php

use App\Http\Controllers\AnimeController;

Route::prefix('/anime')
    ->name('.anime')
    ->group(function() {
        Route::get('/search', [AnimeController::class, 'search'])
            ->middleware('auth.kurozora:optional')
            ->name('.search');

        Route::get('/{anime}', [AnimeController::class, 'view'])
            ->middleware('auth.kurozora:optional')
            ->name('.view');

        Route::get('/{anime}/characters', [AnimeController::class, 'characters'])
            ->name('.characters');

        Route::get('/{anime}/cast', [AnimeController::class, 'cast'])
            ->name('.cast');

        Route::get('/{anime}/related-shows', [AnimeController::class, 'relatedShows'])
            ->middleware('auth.kurozora:optional')
            ->name('.related-shows');

        Route::get('/{anime}/seasons', [AnimeController::class, 'seasons'])
            ->name('.seasons');

        Route::get('/{anime}/studios', [AnimeController::class, 'studiosAnime'])
            ->middleware('kurozora.userauth:optional')
            ->name('.studios');

        Route::post('/{anime}/rate', [AnimeController::class, 'rateAnime'])
            ->middleware('auth.kurozora')
            ->name('.rate');
    });
