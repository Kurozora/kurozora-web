<?php

use App\Http\Controllers\API\v1\SessionController;

Route::prefix('/sessions')
    ->name('.sessions')
    ->group(function () {
        Route::get('/', [SessionController::class, 'index'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::prefix('{session}')
            ->group(function () {
                Route::get('/', [SessionController::class, 'details'])
                    ->middleware(['auth.kurozora'])
                    ->can('view', 'session')
                    ->name('.details');

                Route::post('/delete', [SessionController::class, 'delete'])
                    ->middleware(['auth.kurozora'])
                    ->can('delete', 'session')
                    ->name('.delete');
            });
    });
