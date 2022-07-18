<?php

use App\Http\Controllers\ExplorePageController;

Route::prefix('/explore')
    ->name('.explore')
    ->middleware('auth.kurozora:optional')
    ->group(function () {
        Route::get('/', [ExplorePageController::class, 'index'])
            ->name('.index');

        Route::get('{exploreCategory}', [ExplorePageController::class, 'details'])
            ->name('.details');
    });
