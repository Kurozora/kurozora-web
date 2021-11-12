<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/characters')
    ->name('characters')
    ->group(function () {
        Route::prefix('{character}')
            ->group(function () {
                Route::get('/', AnimeDetails::class)
                    ->name('.details');
            });
    });
