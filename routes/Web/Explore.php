<?php

use App\Http\Livewire\Explore\Details as ExploreDetails;
use App\Providers\RouteServiceProvider;

Route::prefix('/explore')
    ->name('explore')
    ->group(function () {
        Route::redirect('/', RouteServiceProvider::HOME)
            ->name('.index');

        Route::prefix('{exploreCategory}')
            ->group(function () {
                Route::get('/', ExploreDetails::class)
                    ->name('.details');
            });
    });
