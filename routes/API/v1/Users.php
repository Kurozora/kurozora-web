<?php

use App\Http\Controllers\API\v1\AccessTokenController;
use App\Http\Controllers\API\v1\AchievementController;
use App\Http\Controllers\API\v1\FollowingController;
use App\Http\Controllers\API\v1\LibraryController;
use App\Http\Controllers\API\v1\RegistrationController;
use App\Http\Controllers\API\v1\UserBlockController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\UserFavoriteController;
use App\Http\Controllers\Auth\SignInWithAppleController;

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
                Route::get('/achievements', [AchievementController::class, 'index'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.achievements');

                Route::post('/block', [UserBlockController::class, 'blockUser'])
                    ->middleware('auth.kurozora')
                    ->name('.block');

                Route::get('/blocked', [UserBlockController::class, 'index'])
                    ->middleware('auth.kurozora')
                    ->name('.blocked');

                Route::get('/library', [LibraryController::class, 'index'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.library');

                Route::get('/favorites', [UserFavoriteController::class, 'index'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.favorites');

                Route::get('/feed-messages', [UserController::class, 'getFeedMessages'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.feed-messages');

                Route::post('/follow', [FollowingController::class, 'followUser'])
                    ->middleware('auth.kurozora')
                    ->can('follow', 'user')
                    ->name('.follow');

                Route::get('/followers', [FollowingController::class, 'getFollowers'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.followers');

                Route::get('/following', [FollowingController::class, 'getFollowing'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.following');

                Route::get('/reviews', [UserController::class, 'getRatings'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');

                Route::get('/profile', [UserController::class, 'profile'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.profile');
            });

        Route::get('/search/{user:slug}', [UserController::class, 'search'])
            ->middleware('auth.kurozora:optional')
            ->name('.search');

        Route::delete('/delete', [UserController::class, 'delete'])
            ->middleware('auth.kurozora')
            ->name('delete');
    });
