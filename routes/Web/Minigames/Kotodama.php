<?php

use App\Http\Controllers\Web\Minigames\KotodamaController;
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

        Route::get('/leaderboards', [KotodamaController::class, 'leaderboards'])
            ->name('.leaderboards');

        Route::get('/me/stats', [KotodamaController::class, 'stats'])
            ->middleware('auth')
            ->name('.stats');

        Route::get('/versus/{seed}', KotodamaPlayVersus::class)
            ->name('.versus');

        Route::get('/archive', [KotodamaController::class, 'archive'])
            ->middleware(['auth', 'user.is-pro-or-subscribed'])
            ->name('.archive');

        Route::get('/archive/{date}', KotodamaPlayArchive::class)
            ->middleware(['auth', 'user.is-pro-or-subscribed'])
            ->where('date', '\d{4}-\d{2}-\d{2}')
            ->name('.archive.play');
    });
