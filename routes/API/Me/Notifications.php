<?php

use App\Http\Controllers\NotificationController;

Route::prefix('/notifications')
    ->name('.notifications')
    ->group(function() {
        Route::get('/', [NotificationController::class, 'index'])
            ->middleware('auth.kurozora');

        Route::prefix('{notification}')
            ->group(function () {
                Route::get('/', [NotificationController::class, 'details'])
                    ->middleware('auth.kurozora')
                    ->middleware('can:get_notification,notification')
                    ->name('.details');

                Route::post('/delete', [NotificationController::class, 'delete'])
                    ->middleware(['auth.kurozora', 'can:del_notification,notification'])
                    ->name('.delete');
            });

        Route::post('/update', [NotificationController::class, 'update'])
            ->middleware('auth.kurozora')
            ->name('.update');
    });
