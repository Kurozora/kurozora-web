<?php

use App\Livewire\Genre\Details as GenreDetails;
use App\Livewire\Genre\Index as GenreIndex;

Route::prefix('/genres')
    ->name('genres')
    ->group(function () {
        Route::get('/', GenreIndex::class)
            ->name('.index');

        Route::prefix('{genre}')
            ->group(function () {
            Route::get('/', GenreDetails::class)
                ->name('.details');
        });
    });
