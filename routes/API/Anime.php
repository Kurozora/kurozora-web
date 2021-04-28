<?php

use App\Http\Controllers\AnimeController;

Route::prefix('/anime')
    ->name('.anime')
    ->group(function() {
        Route::get('/search', [AnimeController::class, 'search'])
            ->middleware('kurozora.userauth:optional')
            ->name('.search');

        Route::get('/{anime}', [AnimeController::class, 'view'])
            ->middleware('kurozora.userauth:optional')
            ->name('.view');

        Route::get('/{anime}/actors', [AnimeController::class, 'actorsAnime'])
            ->name('.actors');

        Route::get('/{anime}/characters', [AnimeController::class, 'charactersAnime'])
            ->name('.characters');

        Route::get('/{anime}/cast', [AnimeController::class, 'actorCharacterAnime'])
            ->name('.cast');

        Route::get('/{anime}/genres', [AnimeController::class, 'genresAnime'])
            ->name('.genres');

        Route::get('/{anime}/related-shows', [AnimeController::class, 'relatedShowsAnime'])
            ->middleware('kurozora.userauth:optional')
            ->name('.related-shows');

        Route::get('/{anime}/seasons', [AnimeController::class, 'seasonsAnime'])
            ->name('.seasons');

        Route::get('/{anime}/studios', [AnimeController::class, 'studiosAnime'])
            ->middleware('kurozora.userauth:optional')
            ->name('.studios');

        Route::post('/{anime}/rate', [AnimeController::class, 'rateAnime'])
            ->middleware('kurozora.userauth')
            ->name('.rate');
    });
