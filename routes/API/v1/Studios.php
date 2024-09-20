<?php

use App\Http\Controllers\API\v1\StudioController;

Route::prefix('/studios')
    ->name('.studios')
    ->group(function () {
        Route::get('/', [StudioController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{studio}')
            ->group(function () {
                Route::get('/', [StudioController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/predecessors', [StudioController::class, 'predecessors'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.predecessors');

                Route::get('/successors', [StudioController::class, 'successors'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.successors');

                Route::get('/anime', [StudioController::class, 'anime'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.anime');

                Route::get('/literatures', [StudioController::class, 'literatures'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.literatures');

                Route::get('/games', [StudioController::class, 'games'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.games');

                Route::post('/rate', [StudioController::class, 'rateStudio'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');

                Route::get('/reviews', [StudioController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
