<?php

use App\Http\Controllers\API\v1\NotificationController;

Route::prefix('/notifications')
    ->middleware('auth.kurozora')
    ->name('.notifications')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('.index');

        Route::post('/delete', [NotificationController::class, 'delete'])
            ->name('.delete');

        Route::post('/update', [NotificationController::class, 'update'])
            ->name('.update');

        Route::prefix('{notification}')
            ->group(function () {
                Route::get('/', [NotificationController::class, 'details'])
                    ->can('view', 'notification')
                    ->name('.details');

                Route::post('/delete', [NotificationController::class, 'deleteOne'])
                    ->can('delete', 'notification')
                    ->name('.deleteOne');
            });
    });
