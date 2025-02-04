<?php

use App\Livewire\ThemeStore\CreateThemeStoreForm;
use App\Livewire\ThemeStore\Index as ThemeStoreIndex;
use App\Models\AppTheme;

Route::prefix('/theme-store')
    ->name('theme-store')
    ->group(function () {
        Route::get('/', ThemeStoreIndex::class)
            ->name('.index');

        Route::prefix('{appTheme}')
            ->group(function () {
                Route::get('/edit', function (AppTheme $appTheme) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\AppTheme::uriKey() . '/' . $appTheme->id);
                })
                    ->middleware('auth')
                    ->name('.edit');
            });

        Route::get('/create', CreateThemeStoreForm::class)
            ->name('.create');
    });
