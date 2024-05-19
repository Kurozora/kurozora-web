<?php

use App\Livewire\Search\Index as SearchIndex;

Route::prefix('/search')
    ->name('search')
    ->group(function () {
        Route::get('/', SearchIndex::class)
            ->name('.index');
    });
