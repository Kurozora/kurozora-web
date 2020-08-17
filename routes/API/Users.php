<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/users')
    ->name('users.')
    ->group(function() {
        Route::post('/', [RegistrationController::class, 'register']);

        Route::post('/register-siwa', [SignInWithAppleController::class, 'register']);

        Route::post('/login-siwa', [SignInWithAppleController::class, 'login']);

        Route::get('/search', [UserController::class, 'search'])
            ->middleware('kurozora.userauth:optional');

        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/{user}/sessions', [UserController::class, 'getSessions'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_sessions,user');

        Route::post('/{user}/follow', [FollowingController::class, 'followUser'])
            ->middleware('kurozora.userauth')
            ->middleware('can:follow,user');

        Route::get('/{user}/followers', [FollowingController::class, 'getFollowers'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{user}/following', [FollowingController::class, 'getFollowing'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{user}/favorite-anime', [FavoriteAnimeController::class, 'getFavorites'])
            ->middleware('kurozora.userauth');

        Route::post('/{user}/favorite-anime', [FavoriteAnimeController::class, 'addFavorite'])
            ->middleware('kurozora.userauth');

        Route::get('/{user}/profile', [UserController::class, 'profile'])
            ->middleware('kurozora.userauth:optional')
            ->name('profile');

        Route::post('/{user}/profile', [UserController::class, 'updateProfile'])
            ->middleware('kurozora.userauth')
            ->middleware('can:update_profile,user');
    });
