<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/actors')
    ->name('.actors')
    ->group(function() {
        Route::get('/{actor}', [ActorController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('.details');

        Route::get('/{actor}/anime', [ActorController::class, 'anime'])
            ->middleware('kurozora.userauth:optional')
            ->name('.anime');

        Route::get('/{actor}/characters', [ActorController::class, 'characters'])
            ->name('.characters');
    });
