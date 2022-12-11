<?php

use App\Http\Controllers\API\v1\FeedController;

Route::prefix('/feed')
    ->name('.feed')
    ->group(function () {
        Route::post('/', [FeedController::class, 'post'])
            ->middleware('auth.kurozora');

        Route::get('/home', [FeedController::class, 'home'])
            ->middleware('auth.kurozora')
            ->name('.home');

        Route::get('/explore', [FeedController::class, 'explore'])
            ->middleware('auth.kurozora:optional')
            ->name('.global');

        require 'Feed/Messages.php';
    });
