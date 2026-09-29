<?php

use App\Http\Controllers\Web\FeedController;
use App\Http\Controllers\Web\SectionController;
use App\Livewire\Feed\Activity as FeedActivity;

Route::prefix('feed')
    ->name('feed')
    ->group(function () {
        Route::get('/', [FeedController::class, 'index'])
            ->name('.index');

        Route::get('/section', [SectionController::class, 'feed'])
            ->name('.section');

        Route::get('/{feedMessage}/activity', FeedActivity::class)
            ->name('.activity');

        Route::get('/{feedMessage}', [FeedController::class, 'show'])
            ->name('.details');
    });
