<?php

use App\Livewire\Notifications\Index as NotificationsIndex;

Route::prefix('/notifications')
    ->name('notifications')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', NotificationsIndex::class)
            ->name('.index');
    });
