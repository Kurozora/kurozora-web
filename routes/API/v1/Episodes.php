<?php

use App\Http\Controllers\API\v1\EpisodeController;

Route::prefix('/episodes')
    ->name('.episodes')
    ->group(function () {
        Route::get('/', [EpisodeController::class, 'views'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

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

                Route::prefix('rate')
                    ->middleware('auth.kurozora')
                    ->group(function () {
                        Route::post('/', [EpisodeController::class, 'rate'])
                            ->name('.rate');

                        Route::delete('/', [EpisodeController::class, 'deleteRating'])
                            ->name('.delete-rating');
                    });

                Route::get('/reviews', [EpisodeController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');
            });
    });
