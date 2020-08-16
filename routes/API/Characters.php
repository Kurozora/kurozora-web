<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/characters')
    ->name('characters.')
    ->group(function() {
        Route::get('/{character}', [CharacterController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('details');

        Route::get('/{character}/actors', [CharacterController::class, 'actors'])
            ->name('actors');

        Route::get('/{character}/anime', [CharacterController::class, 'anime'])
            ->middleware('kurozora.userauth:optional')
            ->name('anime');
    });
