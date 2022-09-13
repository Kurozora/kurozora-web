<?php

use App\Http\Livewire\Song\Details as SongDetails;

Route::prefix('/songs')
    ->name('songs')
    ->group(function () {
        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', SongDetails::class)
                    ->name('.details');
            });
    });
