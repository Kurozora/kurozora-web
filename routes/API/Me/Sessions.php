<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\SessionController;

Route::prefix('/sessions')
    ->name('.sessions')
    ->group(function () {
        Route::get('/', [MeController::class, 'getSessions'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::prefix('{session}')
            ->group(function () {
                Route::get('/', [SessionController::class, 'details'])
                    ->middleware(['auth.kurozora'])
                    ->can('get_session', 'session')
                    ->name('.details');

                Route::post('/delete', [SessionController::class, 'delete'])
                    ->middleware(['auth.kurozora'])
                    ->can('delete', 'session')
                    ->name('.delete');
            });
    });
