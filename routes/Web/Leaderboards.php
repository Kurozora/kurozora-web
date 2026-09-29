<?php

use App\Http\Controllers\Web\LeaderboardController;

Route::prefix('/leaderboards')
    ->name('leaderboards')
    ->group(function () {
        Route::get('/reputation', [LeaderboardController::class, 'reputation'])
            ->name('.reputation');
    });
