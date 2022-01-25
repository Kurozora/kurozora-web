<?php

use App\Http\Livewire\ThemeStore\CreateThemeStoreForm;
use App\Http\Livewire\ThemeStore\Index as ThemeStoreIndex;

Route::prefix('/theme-store')
    ->name('theme-store')
    ->group(function () {
        Route::get('/', ThemeStoreIndex::class)
            ->name('.index');

        Route::get('/create', CreateThemeStoreForm::class)
            ->name('.create');
    });
