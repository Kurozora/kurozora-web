<?php

use App\Http\Controllers\Web\UserProfileController;
use App\Http\Livewire\Profile\Details;

Route::prefix('/profile')
    ->name('profile')
    ->group(function() {
        Route::get('/settings', [UserProfileController::class, 'settings'])
            ->middleware(['auth', 'verified'])
            ->name('.settings');

        Route::get('/{user}', Details::class)
            ->name('.details');
    });
