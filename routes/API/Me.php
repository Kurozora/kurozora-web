<?php

use App\Http\Controllers\MeController;

Route::prefix('/me')
    ->name('.me')
    ->group(function () {
        Route::get('/', [MeController::class, 'me'])
            ->middleware('auth.kurozora');

        Route::post('/', [MeController::class, 'updateProfile'])
            ->middleware('auth.kurozora')
            ->name('.update');

        Route::get('/followers', [MeController::class, 'getFollowers'])
            ->middleware('auth.kurozora:optional')
            ->name('.followers');

        Route::get('/following', [MeController::class, 'getFollowing'])
            ->middleware('auth.kurozora:optional')
            ->name('.following');

        require 'Me/Access-Tokens.php';
        require 'Me/Favorite-Anime.php';
        require 'Me/Feed-Messages.php';
        require 'Me/Library.php';
        require 'Me/Notifications.php';
        require 'Me/Reminder-Anime.php';
        require 'Me/Sessions.php';
    });
