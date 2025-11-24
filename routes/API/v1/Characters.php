<?php

use App\Http\Controllers\API\v1\CharacterController;

Route::prefix('/characters')
    ->name('.characters')
    ->group(function () {
        Route::get('/', [CharacterController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{character}')
            ->group(function () {
                Route::get('/', [CharacterController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/people', [CharacterController::class, 'people'])
                    ->name('.people');

                Route::get('/anime', [CharacterController::class, 'anime'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.anime');

                Route::get('/literatures', [CharacterController::class, 'literatures'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.literatures');

                Route::get('/games', [CharacterController::class, 'games'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.games');

                Route::prefix('rate')
                    ->middleware('auth.kurozora')
                    ->group(function () {
                        Route::post('/', [CharacterController::class, 'rate'])
                            ->name('.rate');

                        Route::delete('/', [CharacterController::class, 'deleteRating'])
                            ->name('.delete-rating');
                    });

                Route::get('/reviews', [CharacterController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
