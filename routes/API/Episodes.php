<?php

use App\Http\Controllers\EpisodeController;

Route::prefix('/episodes')
    ->name('.episodes')
    ->group(function () {
        Route::prefix('{episode}')
            ->group(function () {
                Route::get('/', [EpisodeController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::post('/watched', [EpisodeController::class, 'watched'])
                    ->middleware('auth.kurozora');

                Route::post('/rate', [EpisodeController::class, 'rateEpisode'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');
            });
    });
