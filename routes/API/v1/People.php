<?php

use App\Http\Controllers\API\v1\PersonController;

Route::prefix('/people')
    ->name('.people')
    ->group(function () {
        Route::get('/', [PersonController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{person}')
            ->group(function () {
                Route::get('/', [PersonController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/anime', [PersonController::class, 'anime'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.anime');

                Route::get('/literatures', [PersonController::class, 'literatures'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.literatures');

                Route::get('/games', [PersonController::class, 'games'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.games');

                Route::get('/characters', [PersonController::class, 'characters'])
                    ->name('.characters');

                Route::post('/rate', [PersonController::class, 'ratePerson'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');

                Route::get('/reviews', [PersonController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
