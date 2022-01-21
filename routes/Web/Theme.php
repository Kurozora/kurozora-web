<?php

use App\Http\Livewire\Theme\CreateThemeForm;
use App\Http\Livewire\Theme\Index as ThemeIndex;

Route::prefix('/themes')
    ->name('themes')
    ->group(function () {
        Route::get('/', ThemeIndex::class)
            ->name('.index');

        Route::get('/create', CreateThemeForm::class)
            ->name('.create');
    });
