<?php

use App\Http\Livewire\Song\Details as SongDetails;
use App\Http\Livewire\Song\Index as SongIndex;

Route::prefix('/songs')
    ->name('songs')
    ->group(function () {
        Route::get('/', SongIndex::class)
            ->name('.index');

        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', SongDetails::class)
                    ->name('.details');
            });
    });
