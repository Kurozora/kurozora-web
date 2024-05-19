<?php

use App\Livewire\Season\Episodes;

Route::prefix('/seasons')
    ->name('seasons')
    ->group(function () {
        Route::prefix('{season}')
            ->group(function () {
                Route::get('/episodes', Episodes::class)
                    ->name('.episodes');
            });
    });
