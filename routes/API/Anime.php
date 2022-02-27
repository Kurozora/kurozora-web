<?php

use App\Http\Controllers\AnimeController;

Route::prefix('/anime')
    ->name('.anime')
    ->group(function () {
        Route::get('/search', [AnimeController::class, 'search'])
            ->middleware('auth.kurozora:optional')
            ->name('.search');

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

                Route::get('/seasons', [AnimeController::class, 'seasons'])
                    ->name('.seasons');

                Route::get('/songs', [AnimeController::class, 'songs'])
                    ->name('.songs');

                Route::get('/staff', [AnimeController::class, 'staff'])
                    ->name('.staff');

                Route::get('/studios', [AnimeController::class, 'studios'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.studios');

                Route::post('/rate', [AnimeController::class, 'rateAnime'])
                    ->middleware('auth.kurozora')
                ->name('.rate');
            });
    });
