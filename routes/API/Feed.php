<?php

use App\Http\Controllers\FeedController;

Route::prefix('/feed')
    ->name('.feed')
    ->group(function() {
        Route::post('/', [FeedController::class, 'post'])
            ->middleware('kurozora.userauth');

        Route::get('/home', [FeedController::class, 'home'])
            ->middleware('kurozora.userauth')
            ->name('.home');

        Route::get('/explore', [FeedController::class, 'explore'])
            ->middleware('kurozora.userauth:optional')
            ->name('.global');

        require 'Feed/Messages.php';
    });
