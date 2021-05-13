<?php

use App\Http\Controllers\AnimeSeasonController;

Route::prefix('/anime-seasons')
    ->name('.seasons')
    ->group(function() {
        Route::get('/{season}', [AnimeSeasonController::class, 'details'])
            ->name('.details');

        Route::get('/{season}/episodes', [AnimeSeasonController::class, 'episodes'])
            ->middleware('auth.kurozora:optional')
            ->name('.episodes');
    });
