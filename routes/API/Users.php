<?php

use App\Http\Controllers\Auth\SignInWithAppleController;
use App\Http\Controllers\FavoriteAnimeController;
use App\Http\Controllers\FollowingController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;

Route::prefix('/users')
    ->name('.users')
    ->group(function() {
        Route::post('/', [RegistrationController::class, 'signUp']);

        Route::post('/signin', [SessionController::class, 'create'])
            ->name('.sign-in');

        Route::post('/signin/siwa', [SignInWithAppleController::class, 'signIn'])
        ->name('.sign-in.siwa');

        Route::post('/reset-password', [UserController::class, 'resetPassword'])
            ->name('.reset-password');

        Route::get('/{user}/favorite-anime', [FavoriteAnimeController::class, 'getFavorites'])
            ->middleware(['auth.kurozora', 'can:get_anime_favorites,user'])
            ->name('.favorite-anime');

        Route::get('/{user}/feed-messages', [UserController::class, 'getFeedMessages'])
            ->middleware('auth.kurozora:optional')
            ->name('.feed-messages');

        Route::post('/{user}/follow', [FollowingController::class, 'followUser'])
            ->middleware(['auth.kurozora', 'can:follow,user'])
            ->name('.follow');

        Route::get('/{user}/followers', [FollowingController::class, 'getFollowers'])
            ->middleware('auth.kurozora:optional')
            ->name('.followers');

        Route::get('/{user}/following', [FollowingController::class, 'getFollowing'])
            ->middleware('auth.kurozora:optional')
            ->name('.following');

        Route::get('/{user}/profile', [UserController::class, 'profile'])
            ->middleware('auth.kurozora:optional')
            ->name('.profile');

        Route::get('/search', [UserController::class, 'search'])
            ->middleware('auth.kurozora:optional')
            ->name('.search');
    });
