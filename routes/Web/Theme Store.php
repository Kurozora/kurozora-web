<?php

use App\Http\Controllers\Web\ThemeStoreController;
use App\Livewire\ThemeStore\CreateThemeStoreForm;
use App\Models\AppTheme;

Route::prefix('/theme-store')
    ->name('theme-store')
    ->group(function () {
        Route::get('/', [ThemeStoreController::class, 'index'])
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
