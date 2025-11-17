<?php

use App\Http\Controllers\API\v1\AnimeController;

Route::prefix('/anime')
    ->name('.anime')
    ->group(function () {
        Route::get('/', [AnimeController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::get('/upcoming', [AnimeController::class, 'upcoming'])
            ->middleware('auth.kurozora:optional')
            ->name('.upcoming');

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/{year}/{season}', [AnimeController::class, 'browseSeason'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');
            });

        Route::prefix('{anime}')
            ->group(function () {
                Route::get('/', [AnimeController::class, 'view'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');

                Route::get('/characters', [AnimeController::class, 'characters'])
                    ->name('.characters');

                Route::get('/cast', [AnimeController::class, 'cast'])
                    ->name('.cast');

                Route::get('/related-shows', [AnimeController::class, 'relatedShows'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-shows');

                Route::get('/related-literatures', [AnimeController::class, 'relatedLiteratures'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-literatures');

                Route::get('/related-games', [AnimeController::class, 'relatedGames'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-games');

                Route::get('/seasons', [AnimeController::class, 'seasons'])
                    ->name('.seasons');

                Route::get('/songs', [AnimeController::class, 'songs'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.songs');

                Route::get('/staff', [AnimeController::class, 'staff'])
                    ->name('.staff');

                Route::get('/studios', [AnimeController::class, 'studios'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.studios');

                Route::get('/more-by-studio', [AnimeController::class, 'moreByStudio'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.more-by-studio');

                Route::post('/rate', [AnimeController::class, 'rateAnime'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');

                Route::get('/reviews', [AnimeController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
