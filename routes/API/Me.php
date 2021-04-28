<?php

use App\Http\Controllers\MeController;

Route::prefix('/me')
    ->name('.me')
    ->group(function() {
        Route::get('/', [MeController::class, 'me'])
            ->middleware('kurozora.userauth');

        Route::post('/', [MeController::class, 'updateProfile'])
            ->middleware('kurozora.userauth')
            ->name('.update');

        Route::get('/followers', [MeController::class, 'getFollowers'])
            ->middleware('kurozora.userauth:optional')
            ->name('.followers');

        Route::get('/following', [MeController::class, 'getFollowing'])
            ->middleware('kurozora.userauth:optional')
            ->name('.following');

        require 'Me/Favorite-Anime.php';
        require 'Me/Feed-Messages.php';
        require 'Me/Library.php';
        require 'Me/Notifications.php';
        require 'Me/Reminder-Anime.php';
        require 'Me/Sessions.php';
    });
