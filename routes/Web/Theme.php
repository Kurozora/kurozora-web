<?php

use App\Http\Livewire\Theme\Index as ThemeIndex;
use App\Http\Livewire\Theme\CreateThemeForm;

Route::prefix('/themes')
    ->name('themes')
    ->group(function () {
        Route::get('/', ThemeIndex::class);

        Route::get('/create', CreateThemeForm::class)
            ->name('.create');
    });
