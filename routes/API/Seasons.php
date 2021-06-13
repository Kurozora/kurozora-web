<?php

use App\Http\Controllers\SeasonController;

Route::prefix('/seasons')
    ->name('.seasons')
    ->group(function() {
        Route::prefix('{season}')
            ->group(function () {
                Route::get('/', [SeasonController::class, 'details'])
                    ->name('.details');

                Route::get('/episodes', [SeasonController::class, 'episodes'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.episodes');
            });
    });
