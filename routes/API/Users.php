<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/users')
    ->name('users.')
    ->group(function() {
        Route::post('/', [RegistrationController::class, 'signup']);

        Route::post('/signin', [SessionController::class, 'create']);

        Route::post('/signup/siwa', [SignInWithAppleController::class, 'signup']);

        Route::post('/signin/siwa', [SignInWithAppleController::class, 'signin']);

        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::post('/{user}/follow', [FollowingController::class, 'followUser'])
            ->middleware('kurozora.userauth')
            ->middleware('can:follow,user');

        Route::get('/{user}/followers', [FollowingController::class, 'getFollowers'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{user}/following', [FollowingController::class, 'getFollowing'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{user}/favorite-anime', [FavoriteAnimeController::class, 'getFavorites'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_anime_favorites,user');

        Route::get('/{user}/profile', [UserController::class, 'profile'])
            ->middleware('kurozora.userauth:optional')
            ->name('profile');

        Route::get('/search', [UserController::class, 'search'])
            ->middleware('kurozora.userauth:optional');
    });
