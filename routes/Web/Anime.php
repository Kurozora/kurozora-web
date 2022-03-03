<?php

use App\Http\Livewire\Anime\Cast as AnimeCast;
use App\Http\Livewire\Anime\Details as AnimeDetails;
use App\Http\Livewire\Anime\RelatedShows;
use App\Http\Livewire\Anime\Songs as AnimeSongs;
use App\Http\Livewire\Season\Details as SeasonDetails;

Route::prefix('/anime')
    ->name('anime')
    ->group(function () {
        Route::prefix('{anime}')
            ->group(function () {
                Route::get('/', AnimeDetails::class)
                    ->name('.details');

                Route::get('/cast', AnimeCast::class)
                    ->name('.cast');

                Route::get('/seasons', SeasonDetails::class)
                    ->name('.seasons');

                Route::get('/songs', AnimeSongs::class)
                    ->name('.songs');

                Route::get('/related-shows', RelatedShows::class)
                    ->name('.related-shows');
            });
    });
