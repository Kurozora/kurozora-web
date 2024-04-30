<?php

use App\Http\Controllers\API\v1\SongController;

Route::prefix('/songs')
    ->name('.songs')
    ->group(function () {
        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', [SongController::class, 'view'])
                    ->name('.view');

                Route::get('/anime', [SongController::class, 'anime'])
                    ->name('.anime');

                Route::get('/games', [SongController::class, 'games'])
                    ->name('.games');

                Route::post('/rate', [SongController::class, 'rateSong'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');

                Route::get('/reviews', [SongController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
