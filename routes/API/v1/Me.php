<?php

use App\Http\Controllers\API\v1\MeController;

Route::prefix('/me')
    ->middleware('auth.kurozora')
    ->name('.me')
    ->group(function () {
        Route::get('/', [MeController::class, 'me']);

        Route::post('/', [MeController::class, 'updateProfile'])
            ->name('.update');

        Route::get('/followers', [MeController::class, 'getFollowers'])
            ->name('.followers');

        Route::get('/following', [MeController::class, 'getFollowing'])
            ->name('.following');

        Route::get('/reviews', [MeController::class, 'getRatings'])
            ->name('.reviews');

        require 'Me/Access-Tokens.php';
        require 'Me/Favorites.php';
        require 'Me/Feed-Messages.php';
        require 'Me/Library.php';
        require 'Me/Notifications.php';
        require 'Me/Recap.php';
        require 'Me/Reminders.php';
        require 'Me/Sessions.php';
    });
