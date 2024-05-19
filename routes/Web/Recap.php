<?php

use App\Livewire\Recap\Index as RecapIndex;

Route::prefix('/recap')
    ->name('recap')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', RecapIndex::class)
            ->name('.index');
    });
