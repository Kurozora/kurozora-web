<?php

use App\Http\Controllers\API\v1\UserReminderController;

// TODO: - Delete this in favor of reminders endpoint
Route::prefix('/reminder-anime')
    ->name('.reminder-anime')
    ->group(function () {
        Route::get('/', [UserReminderController::class, 'tempIndex'])
            ->name('.index');

        Route::post('/', [UserReminderController::class, 'create'])
            ->name('.create');

        Route::get('/download', [UserReminderController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });

Route::prefix('/reminders')
    ->name('.reminders')
    ->group(function () {
        Route::get('/', [UserReminderController::class, 'index'])
            ->name('.index');

        Route::post('/', [UserReminderController::class, 'create'])
            ->name('.create');

        Route::get('/download', [UserReminderController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });
