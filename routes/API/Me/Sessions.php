<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\SessionController;

Route::prefix('/sessions')
    ->name('.sessions')
    ->group(function () {
        Route::get('/', [MeController::class, 'getSessions'])
            ->middleware('auth.kurozora');

        Route::prefix('{session}')
            ->group(function () {
                Route::get('/', [SessionController::class, 'details'])
                    ->middleware('auth.kurozora')
                    ->middleware('can:get_session,session')
                    ->name('.details');

                Route::post('/update', [SessionController::class, 'update'])
                    ->middleware('auth.kurozora')
                    ->middleware('can:update_session,session')
                    ->name('.update');

                Route::post('/delete', [SessionController::class, 'delete'])
                    ->middleware(['auth.kurozora', 'can:delete_session,session'])
                    ->name('.delete');
            });
    });
