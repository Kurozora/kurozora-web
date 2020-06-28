<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime')->group(function() {
    Route::get('/search', [AnimeController::class, 'search'])
        ->middleware('kurozora.userauth:optional');

    Route::get('/{anime}', [AnimeController::class, 'view'])
        ->middleware('kurozora.userauth:optional');

    Route::get('/{anime}/actors', [AnimeController::class, 'actorsAnime']);

    Route::get('/{anime}/characters', [AnimeController::class, 'charactersAnime']);

    Route::get('/{anime}/cast', [AnimeController::class, 'actorCharactersAnime']);

    Route::get('/{anime}/seasons', [AnimeController::class, 'seasonsAnime']);

    Route::post('/{anime}/rate', [AnimeController::class, 'rateAnime'])
        ->middleware('kurozora.userauth');
});
