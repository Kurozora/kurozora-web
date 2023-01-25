<?php

use App\Http\Controllers\API\v1\StudioController;

Route::prefix('/studios')
    ->name('.studios')
    ->group(function () {
        Route::prefix('{studio}')
            ->group(function () {
                Route::get('/', [StudioController::class, 'details'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.details');

                Route::get('/anime', [StudioController::class, 'anime'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.anime');

                Route::get('/manga', [StudioController::class, 'manga'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.manga');
            });
    });
