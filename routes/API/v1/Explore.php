<?php

use App\Http\Controllers\API\v1\ExploreCategoryController;

Route::prefix('/explore')
    ->name('.explore')
    ->middleware('auth.kurozora:optional')
    ->group(function () {
        Route::get('/', [ExploreCategoryController::class, 'index'])
            ->name('.index');

        Route::get('{exploreCategory}', [ExploreCategoryController::class, 'details'])
            ->name('.details');
    });
