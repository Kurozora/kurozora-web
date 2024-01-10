<?php

use App\Http\Controllers\API\v1\ScheduleController;

Route::prefix('/schedule')
    ->name('.schedule')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'view'])
            ->middleware('auth.kurozora:optional')
            ->name('.view');
    });
