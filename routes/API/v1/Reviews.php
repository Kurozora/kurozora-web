<?php

use App\Http\Controllers\API\v1\MediaRatingController;

Route::prefix('/reviews')
    ->name('.reviews')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/categories', [MediaRatingController::class, 'categories'])
            ->middleware('auth.kurozora:optional')
            ->name('.categories');

        Route::prefix('{mediaRating}')
            ->group(function () {
                Route::get('/', [MediaRatingController::class, 'details'])
                    ->name('.details');

                Route::delete('/delete', [MediaRatingController::class, 'delete'])
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->name('.delete');

                Route::post('/elevate', [MediaRatingController::class, 'elevate'])
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->name('.elevate');

                Route::post('/vote', [MediaRatingController::class, 'vote'])
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->name('.vote');

                Route::post('/report', [MediaRatingController::class, 'report'])
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->name('.report');
            });
    });
