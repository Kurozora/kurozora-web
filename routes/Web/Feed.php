<?php

use App\Livewire\Feed\Detail as FeedDetail;
use App\Livewire\Feed\Index as FeedIndex;

Route::prefix('feed')
    ->name('feed')
    ->group(function () {
        Route::get('/', FeedIndex::class)
            ->name('.index');

        Route::get('/{feedMessage}', FeedDetail::class)
            ->name('.details');
    });
