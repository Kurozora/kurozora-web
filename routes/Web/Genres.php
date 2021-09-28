<?php

use App\Http\Livewire\Genre\Index as GenreIndex;

Route::prefix('/genres')
    ->name('genres')
    ->group(function() {
        Route::get('/', GenreIndex::class)
            ->name('.index');
    });
