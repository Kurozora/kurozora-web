<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/me')
    ->name('me.')
    ->group(function() {
        Route::get('/', [MeController::class, 'me'])
            ->middleware('kurozora.userauth');

        Route::post('/', [MeController::class, 'updateProfile'])
            ->middleware('kurozora.userauth');

        Route::get('/followers', [MeController::class, 'getFollowers'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/following', [MeController::class, 'getFollowing'])
            ->middleware('kurozora.userauth:optional');

        require 'Me/Favorite-Anime.php';
        require 'Me/Feed-Messages.php';
        require 'Me/Library.php';
        require 'Me/Notifications.php';
        require 'Me/Reminder-Anime.php';
        require 'Me/Sessions.php';
    });
