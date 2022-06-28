<?php

use App\Http\Controllers\SearchController;

Route::prefix('/search')
    ->name('.search')
    ->group(function () {
        Route::get('/', [SearchController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::get('/suggestions', [SearchController::class, 'suggestions'])
            ->middleware('auth.kurozora:optional')
            ->name('.suggestions');
    });
