<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/people')
    ->name('people')
    ->group(function () {
        Route::prefix('{person}')
            ->group(function () {
                Route::get('/', AnimeDetails::class)
                    ->name('.details');
            });
    });
