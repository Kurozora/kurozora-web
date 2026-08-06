<?php

use App\Livewire\Compare\Index as CompareIndex;

Route::prefix('/compare')
    ->name('compare')
    ->group(function () {
        Route::get('/', CompareIndex::class)
            ->name('.index');
    });

