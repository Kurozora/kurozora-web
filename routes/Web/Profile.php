<?php

use App\Http\Controllers\Web\UserProfileController;
use App\Http\Livewire\Profile\Details;

Route::prefix('/profile')
    ->name('profile')
    ->group(function () {
        Route::get('/settings', [UserProfileController::class, 'settings'])
            ->middleware(['auth', 'verified'])
            ->name('.settings');

        Route::get('/{user}', Details::class)
            ->name('.details');
    });

Route::prefix('/me')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', function () {
            return redirect(route('profile.details', ['user' => Auth::user()]));
        });
    });
