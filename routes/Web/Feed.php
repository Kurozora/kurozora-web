<?php

use App\Http\Controllers\Web\FeedController;
use App\Http\Controllers\Web\SectionController;

Route::prefix('feed')
    ->name('feed')
    ->group(function () {
        Route::get('/', [FeedController::class, 'index'])
            ->name('.index');

        Route::get('/section', [SectionController::class, 'feed'])
            ->name('.section');

        Route::get('/{feedMessage}/activity', [FeedController::class, 'activity'])
            ->name('.activity');

        Route::get('/{feedMessage}', [FeedController::class, 'show'])
            ->name('.details');
    });
