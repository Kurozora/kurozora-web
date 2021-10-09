<?php

use App\Http\Controllers\PersonController;

Route::prefix('/people')
    ->name('.people')
    ->group(function () {
        Route::prefix('{person}')
            ->group(function () {
                Route::get('/', [PersonController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/anime', [PersonController::class, 'anime'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.anime');

                Route::get('/characters', [PersonController::class, 'characters'])
                    ->name('.characters');
            });
    });
