<?php

use App\Http\Controllers\Web\NotificationController;

Route::prefix('/notifications')
    ->name('notifications')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('.index');

        Route::get('/unread', function () {
            return ['hasUnread' => auth()->user()->unreadNotifications()->exists()];
        })
            ->name('.unread');
    });
