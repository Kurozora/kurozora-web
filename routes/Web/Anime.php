<?php

use App\Http\Livewire\Anime\Details;

Route::prefix('/anime')
    ->name('anime')
    ->group(function() {
        Route::get('/{anime}', Details::class)
            ->name('.details');
    });
