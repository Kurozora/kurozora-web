<?php

use App\Http\Controllers\NotificationController;

Route::prefix('/notifications')
    ->name('.notifications')
    ->group(function() {
        Route::get('/', [NotificationController::class, 'index'])
            ->middleware('kurozora.userauth');

        Route::get('/{notification}', [NotificationController::class, 'details'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_notification,notification')
            ->name('.details');

        Route::post('/{notification}/delete', [NotificationController::class, 'delete'])
            ->middleware(['kurozora.userauth', 'can:del_notification,notification'])
            ->name('.delete');

        Route::post('/update', [NotificationController::class, 'update'])
            ->middleware('kurozora.userauth')
            ->name('.update');
    });
