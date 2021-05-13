<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\SessionController;

Route::prefix('/sessions')
    ->name('.sessions')
    ->group(function() {
        Route::get('/', [MeController::class, 'getSessions'])
            ->middleware('auth.kurozora');

        Route::get('/{session}', [SessionController::class, 'details'])
            ->middleware('auth.kurozora')
            ->middleware('can:get_session,session')
            ->name('.details');

        Route::post('/{session}/update', [SessionController::class, 'update'])
            ->middleware('auth.kurozora')
            ->middleware('can:update_session,session')
            ->name('.update');

        Route::post('/{session}/delete', [SessionController::class, 'delete'])
            ->middleware(['auth.kurozora', 'can:delete_session,session'])
            ->name('.delete');
    });
