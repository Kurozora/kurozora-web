<?php

use App\Http\Controllers\API\v1\UserReminderController;

Route::prefix('/reminder-anime')
    ->name('.reminder-anime')
    ->group(function () {
        Route::get('/', [UserReminderController::class, 'index'])
            ->middleware('auth.kurozora');

        Route::post('/', [UserReminderController::class, 'create'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::get('/download', [UserReminderController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });
