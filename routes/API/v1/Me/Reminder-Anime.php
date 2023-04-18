<?php

use App\Http\Controllers\API\v1\ReminderAnimeController;

Route::prefix('/reminder-anime')
    ->name('.reminder-anime')
    ->group(function () {
        Route::get('/', [ReminderAnimeController::class, 'index'])
            ->middleware('auth.kurozora');

        Route::post('/', [ReminderAnimeController::class, 'create'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::get('/download', [ReminderAnimeController::class, 'download'])
            ->middleware('auth.basic')
            ->name('.download');
    });
