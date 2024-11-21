<?php

use App\Livewire\Season\Episodes;
use App\Models\Season;

Route::prefix('/seasons')
    ->name('seasons')
    ->group(function () {
        Route::prefix('{season}')
            ->group(function () {
                Route::get('/edit', function (Season $season) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Season::uriKey() . '/' . $season->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/episodes', Episodes::class)
                    ->name('.episodes');
            });
    });
