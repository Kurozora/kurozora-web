<?php

use App\Http\Controllers\PersonController;

Route::prefix('/people')
    ->name('.people')
    ->group(function() {
        Route::get('/{person}', [PersonController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('.details');

        Route::get('/{person}/anime', [PersonController::class, 'anime'])
            ->middleware('kurozora.userauth:optional')
            ->name('.anime');

        Route::get('/{person}/characters', [PersonController::class, 'characters'])
            ->name('.characters');
    });
