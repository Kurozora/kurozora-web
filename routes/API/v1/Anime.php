<?php

use App\Http\Controllers\API\v1\AnimeController;

Route::prefix('/anime')
    ->name('.anime')
    ->group(function () {
        Route::get('/upcoming', [AnimeController::class, 'upcoming'])
            ->middleware('auth.kurozora:optional')
            ->name('.upcoming');

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

                Route::get('/more-by-studio', [AnimeController::class, 'moreByStudio'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.more-by-studio');

                Route::post('/rate', [AnimeController::class, 'rateAnime'])
                    ->middleware('auth.kurozora')
                ->name('.rate');
            });
    });
