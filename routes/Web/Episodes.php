<?php

use App\Http\Livewire\Episode\Details as EpisodeDetails;

Route::prefix('/episodes')
    ->name('episodes')
    ->group(function () {
        Route::prefix('{episode}')
            ->group(function () {
                Route::get('/', EpisodeDetails::class)
                    ->name('.details');
            });
    });
