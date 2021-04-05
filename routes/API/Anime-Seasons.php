<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime-seasons')
    ->name('.seasons')
    ->group(function() {
        Route::get('/{season}', [AnimeSeasonController::class, 'details'])
            ->name('.details');

        Route::get('/{season}/episodes', [AnimeSeasonController::class, 'episodes'])
            ->middleware('kurozora.userauth:optional')
            ->name('.episodes');
    });
