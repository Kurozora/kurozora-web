<?php

use App\Livewire\Explore\Details as ExploreDetails;

Route::prefix('/explore')
    ->name('explore')
    ->group(function () {
        Route::redirect('/', '/')
            ->name('.index');

        Route::prefix('{exploreCategory}')
            ->middleware(['explore.always-enabled'])
            ->group(function () {
                Route::get('/', ExploreDetails::class)
                    ->name('.details');
            });
    });
