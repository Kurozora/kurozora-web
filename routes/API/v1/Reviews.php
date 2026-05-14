<?php

use App\Http\Controllers\API\v1\MediaRatingController;

Route::prefix('/reviews')
    ->name('.reviews')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::prefix('{mediaRating}')
            ->group(function () {
                Route::get('/', [MediaRatingController::class, 'details'])
                    ->name('.details');

                Route::delete('/delete', [MediaRatingController::class, 'delete'])
                    ->middleware('auth.kurozora')
                    ->name('.delete');
            });
    });
