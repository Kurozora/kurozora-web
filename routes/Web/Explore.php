<?php

use App\Livewire\Explore\Details as ExploreDetails;
use App\Providers\RouteServiceProvider;

Route::prefix('/explore')
    ->name('explore')
    ->group(function () {
        Route::redirect('/', RouteServiceProvider::HOME)
            ->name('.index');

        Route::prefix('{exploreCategory}')
            ->middleware(['explore.always-enabled'])
            ->group(function () {
                Route::get('/', ExploreDetails::class)
                    ->name('.details');
            });
    });
