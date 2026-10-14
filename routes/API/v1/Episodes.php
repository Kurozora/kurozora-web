<?php

use App\Http\Controllers\API\v1\EpisodeController;

Route::prefix('/episodes')
    ->name('.episodes')
    ->middleware('cache.headers:private;no_cache;etag')
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

                Route::prefix('watched')
                    ->middleware('auth.kurozora')
                    ->group(function () {
                        Route::post('/', [EpisodeController::class, 'watched'])
                            ->name('.watched');

                        Route::delete('/', [EpisodeController::class, 'clearWatched'])
                            ->name('.delete-watched');
                    });

                Route::prefix('rate')
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
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
