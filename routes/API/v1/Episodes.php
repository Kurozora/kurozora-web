<?php

use App\Http\Controllers\API\v1\EpisodeController;

Route::prefix('/episodes')
    ->name('.episodes')
    ->group(function () {
        Route::prefix('{episode}')
            ->group(function () {
                Route::get('/', [EpisodeController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/suggestions', [EpisodeController::class, 'suggestions'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.suggestions');

                Route::post('/watched', [EpisodeController::class, 'watched'])
                    ->middleware('auth.kurozora');

                Route::post('/rate', [EpisodeController::class, 'rateEpisode'])
                    ->middleware('auth.kurozora')
                    ->name('.rate');

                Route::get('/reviews', [EpisodeController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
