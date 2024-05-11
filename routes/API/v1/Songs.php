<?php

use App\Http\Controllers\API\v1\SongController;

Route::prefix('/songs')
    ->name('.songs')
    ->group(function () {
        Route::get('/', [SongController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', [SongController::class, 'view'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');

                Route::get('/anime', [SongController::class, 'anime'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.anime');

                Route::get('/games', [SongController::class, 'games'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.games');

                Route::post('/rate', [SongController::class, 'rateSong'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');

                Route::get('/reviews', [SongController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
