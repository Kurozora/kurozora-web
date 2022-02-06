<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\Auth\SignInWithAppleController;
use App\Http\Controllers\FavoriteAnimeController;
use App\Http\Controllers\FollowingController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UserController;

Route::prefix('/users')
    ->name('.users')
    ->group(function () {
        Route::post('/', [RegistrationController::class, 'signUp']);

        Route::post('/signin', [AccessTokenController::class, 'create'])
            ->name('.sign-in');

        Route::post('/reset-password', [UserController::class, 'resetPassword'])
            ->name('.reset-password');

        Route::prefix('/siwa')
            ->name('.siwa')
            ->group(function () {
                Route::post('/signin', [SignInWithAppleController::class, 'signIn'])
                    ->name('.sign-in');

                Route::post('/update', [SignInWithAppleController::class, 'update'])
                    ->name('.update');
            });

        Route::prefix('{user}')
            ->group(function () {
                Route::get('/favorite-anime', [FavoriteAnimeController::class, 'getFavorites'])
                    ->middleware(['auth.kurozora', 'can:get_anime_favorites,user'])
                    ->name('.favorite-anime');

                Route::get('/feed-messages', [UserController::class, 'getFeedMessages'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.feed-messages');

                Route::post('/follow', [FollowingController::class, 'followUser'])
                    ->middleware(['auth.kurozora', 'can:follow,user'])
                    ->name('.follow');

                Route::get('/followers', [FollowingController::class, 'getFollowers'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.followers');

                Route::get('/following', [FollowingController::class, 'getFollowing'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.following');

                Route::get('/profile', [UserController::class, 'profile'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.profile');
            });

        Route::get('/search', [UserController::class, 'search'])
            ->middleware('auth.kurozora:optional')
            ->name('.search');

        Route::delete('/delete', [UserController::class, 'delete'])
            ->middleware('auth.kurozora')
            ->name('delete');
    });
