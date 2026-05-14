<?php

use App\Http\Controllers\API\v1\ScheduleController;

Route::prefix('/schedule')
    ->name('.schedule')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'view'])
            ->middleware('auth.kurozora:optional')
            ->name('.view');
    });
