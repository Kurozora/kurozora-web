<?php

use App\Http\Livewire\Theme\Details as ThemeDetails;
use App\Http\Livewire\Theme\Index as ThemeIndex;

Route::prefix('/themes')
    ->name('themes')
    ->group(function () {
        Route::get('/', ThemeIndex::class)
            ->name('.index');

        Route::prefix('{theme}')
            ->group(function () {
                Route::get('/', ThemeDetails::class)
                    ->name('.details');
            });
    });
