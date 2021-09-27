<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;
use App\Http\Livewire\Anime\RelatedShows;
use App\Http\Livewire\Season\Details as SeasonDetails;

Route::prefix('/anime')
    ->name('anime')
    ->group(function() {
        Route::prefix('{anime}')
            ->group(function () {
                Route::get('/', AnimeDetails::class)
                    ->name('.details');

                Route::get('/seasons', SeasonDetails::class)
                    ->name('.seasons');

                Route::get('/related-shows', RelatedShows::class)
                    ->name('.related-shows');
            });
    });
