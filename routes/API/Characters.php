<?php

use App\Http\Controllers\CharacterController;

Route::prefix('/characters')
    ->name('.characters')
    ->group(function() {
        Route::get('/{character}', [CharacterController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('.details');

        Route::get('/{character}/people', [CharacterController::class, 'people'])
            ->name('.people');

        Route::get('/{character}/anime', [CharacterController::class, 'anime'])
            ->middleware('kurozora.userauth:optional')
            ->name('.anime');
    });
