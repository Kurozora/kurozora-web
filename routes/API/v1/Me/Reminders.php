<?php

use App\Http\Controllers\API\v1\UserReminderController;

Route::prefix('/reminders')
    ->name('.reminders')
    ->group(function () {
        Route::get('/', [UserReminderController::class, 'index'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::post('/', [UserReminderController::class, 'create'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::get('/download', [UserReminderController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });
