<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/me')
    ->name('me.')
    ->group(function() {
        Route::get('/', [MeController::class, 'me'])
            ->middleware('kurozora.userauth');

        require 'Me/Library.php';
        require 'Me/Notifications.php';
        require 'Me/Reminder-Anime.php';
    });
