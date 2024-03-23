<?php

use App\Http\Controllers\API\v1\MediaRatingController;

Route::prefix('/reviews')
    ->name('.reviews')
    ->group(function () {
        Route::prefix('{mediaRating}')
            ->group(function () {
                Route::get('/', [MediaRatingController::class, 'details'])
                    ->name('.details');
            });
    });
