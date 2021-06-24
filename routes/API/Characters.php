<?php

use App\Http\Controllers\CharacterController;

Route::prefix('/characters')
    ->name('.characters')
    ->group(function() {
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
            });
    });
