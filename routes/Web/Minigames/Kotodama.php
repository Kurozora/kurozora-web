<?php

use App\Livewire\Minigames\Kotodama\ArchiveIndex as KotodamaArchiveIndex;
use App\Livewire\Minigames\Kotodama\Leaderboards as KotodamaLeaderboards;
use App\Livewire\Minigames\Kotodama\MyStats as KotodamaMyStats;
use App\Livewire\Minigames\Kotodama\PlayArchive as KotodamaPlayArchive;
use App\Livewire\Minigames\Kotodama\PlayDaily as KotodamaPlayDaily;
use App\Livewire\Minigames\Kotodama\PlayUnlimited as KotodamaPlayUnlimited;
use App\Livewire\Minigames\Kotodama\PlayVersus as KotodamaPlayVersus;

Route::prefix('/kotodama')
    ->name('kotodama')
    ->group(function () {
        Route::get('/', KotodamaPlayDaily::class)
            ->middleware('auth')
            ->name('.daily');

        Route::get('/unlimited', KotodamaPlayUnlimited::class)
            ->name('.unlimited');

        Route::get('/leaderboards', KotodamaLeaderboards::class)
            ->name('.leaderboards');

        Route::get('/me/stats', KotodamaMyStats::class)
            ->middleware('auth')
            ->name('.stats');

        Route::get('/versus/{seed}', KotodamaPlayVersus::class)
            ->name('.versus');

        Route::get('/archive', KotodamaArchiveIndex::class)
            ->middleware(['auth', 'user.is-pro-or-subscribed'])
            ->name('.archive');

        Route::get('/archive/{date}', KotodamaPlayArchive::class)
            ->middleware(['auth', 'user.is-pro-or-subscribed'])
            ->where('date', '\d{4}-\d{2}-\d{2}')
            ->name('.archive.play');
    });
