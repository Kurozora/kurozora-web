<?php

use App\Http\Controllers\API\v1\SeasonController;

Route::prefix('/seasons')
    ->name('.seasons')
    ->group(function () {
        Route::get('/', [SeasonController::class, 'views'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::prefix('{season}')
            ->group(function () {
                Route::get('/', [SeasonController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/episodes', [SeasonController::class, 'episodes'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.episodes');

                Route::post('/watched', [SeasonController::class, 'watched'])
                    ->middleware('auth.kurozora')
                    ->name('.watched');
            });
    });
