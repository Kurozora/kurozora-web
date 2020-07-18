<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime')
    ->name('anime.')
    ->group(function() {
        Route::get('/search', [AnimeController::class, 'search'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{anime}', [AnimeController::class, 'view'])
            ->middleware('kurozora.userauth:optional')
            ->name('view');

        Route::get('/{anime}/actors', [AnimeController::class, 'actorsAnime'])
            ->name('actors');

        Route::get('/{anime}/characters', [AnimeController::class, 'charactersAnime'])
            ->name('characters');

        Route::get('/{anime}/cast', [AnimeController::class, 'actorCharacterAnime'])
            ->name('cast');

        Route::get('/{anime}/relations', [AnimeController::class, 'relationsAnime'])
            ->middleware('kurozora.userauth:optional')
            ->name('relations');

        Route::get('/{anime}/seasons', [AnimeController::class, 'seasonsAnime'])
            ->name('seasons');

        Route::post('/{anime}/rate', [AnimeController::class, 'rateAnime'])
            ->middleware('kurozora.userauth');
    });
