<?php

use App\Livewire\Explore\Details as ExploreDetails;
use App\Models\ExploreCategory;
use App\Models\Genre;
use App\Models\Theme;

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

                Route::get('/section', function (ExploreCategory $exploreCategory) {
                    return view('components.explore-category-section', [
                        'exploreCategory' => $exploreCategory,
                        'genre' => Genre::firstWhere('slug', request()->string('genre')),
                        'theme' => Theme::firstWhere('slug', request()->string('theme')),
                    ]);
                })
                    ->middleware('auth')
                    ->name('.section');
            });
    });
