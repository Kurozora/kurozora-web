<?php

use App\Http\Controllers\NotificationController;

Route::prefix('/notifications')
    ->name('.notifications')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->middleware('auth.kurozora');

        Route::prefix('{databaseNotification}')
            ->group(function () {
                Route::get('/', [NotificationController::class, 'details'])
                    ->middleware(['auth.kurozora'])
                    ->can('view', 'databaseNotification')
                    ->name('.details');

                Route::post('/delete', [NotificationController::class, 'delete'])
                    ->middleware(['auth.kurozora'])
                    ->can('delete', 'databaseNotification')
                    ->name('.delete');
            });

        Route::post('/update', [NotificationController::class, 'update'])
            ->middleware('auth.kurozora')
            ->name('.update');
    });
