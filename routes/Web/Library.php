<?php

use App\Http\Livewire\Library\Index as LibraryIndex;

Route::prefix('/library')
    ->name('library')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', LibraryIndex::class)
            ->name('.index');
    });
