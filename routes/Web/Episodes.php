<?php

use App\Livewire\Episode\Details as EpisodeDetails;
use App\Livewire\Episode\Reviews as EpisodeReviews;

Route::prefix('/episodes')
    ->name('episodes')
    ->group(function () {
        Route::prefix('{episode}')
            ->group(function () {
                Route::get('/', EpisodeDetails::class)
                    ->name('.details');

                Route::get('/reviews', EpisodeReviews::class)
                    ->name('.reviews');
            });
    });
