<?php

use App\Http\Controllers\API\v1\SessionController;

Route::prefix('/sessions')
    ->middleware('auth.kurozora')
    ->name('.sessions')
    ->group(function () {
        Route::get('/', [SessionController::class, 'index'])
            ->name('.index');

        Route::prefix('{session}')
            ->group(function () {
                Route::get('/', [SessionController::class, 'details'])
                    ->can('view', 'session')
                    ->name('.details');

                Route::post('/delete', [SessionController::class, 'delete'])
                    ->can('delete', 'session')
                    ->name('.delete');
            });
    });
