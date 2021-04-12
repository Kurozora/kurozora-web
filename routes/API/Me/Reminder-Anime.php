<?php

use App\Http\Controllers\ReminderAnimeController;

Route::prefix('/reminder-anime')
    ->name('.reminder-anime')
    ->group(function() {
        Route::get('/', [ReminderAnimeController::class, 'getReminders'])
            ->middleware('kurozora.userauth');

        Route::post('/', [ReminderAnimeController::class, 'addReminder'])
            ->middleware('kurozora.userauth')
            ->name('.create');

        Route::get('/download', [ReminderAnimeController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });
