<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/notifications')
    ->name('notifications.')
    ->group(function() {
        Route::get('/{notification}', [NotificationController::class, 'details'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_notification,notification')
            ->name('details');

        Route::post('/{notification}/delete', [NotificationController::class, 'delete'])
            ->middleware('kurozora.userauth')
            ->middleware('can:del_notification,notification');

        Route::post('/update', [NotificationController::class, 'update'])
            ->middleware('kurozora.userauth');
    });
