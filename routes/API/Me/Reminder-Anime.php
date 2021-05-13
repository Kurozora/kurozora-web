<?php

use App\Http\Controllers\ReminderAnimeController;

Route::prefix('/reminder-anime')
    ->name('.reminder-anime')
    ->group(function() {
        Route::get('/', [ReminderAnimeController::class, 'getReminders'])
            ->middleware('auth.kurozora');

        Route::post('/', [ReminderAnimeController::class, 'addReminder'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::get('/download', [ReminderAnimeController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });
