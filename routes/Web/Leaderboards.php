<?php

use App\Livewire\Leaderboards\Reputation as ReputationLeaderboard;

Route::prefix('/leaderboards')
    ->name('leaderboards')
    ->group(function () {
        Route::get('/reputation', ReputationLeaderboard::class)
            ->name('.reputation');
    });
