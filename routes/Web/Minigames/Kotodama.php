<?php

use App\Http\Controllers\Web\Minigames\KotodamaController;

Route::prefix('/kotodama')
    ->name('kotodama')
    ->group(function () {
        Route::get('/', [KotodamaController::class, 'daily'])
            ->middleware('auth')
            ->name('.daily');

        Route::get('/unlimited', [KotodamaController::class, 'unlimited'])
            ->name('.unlimited');

        Route::get('/leaderboards', [KotodamaController::class, 'leaderboards'])
            ->name('.leaderboards');

        Route::get('/me/stats', [KotodamaController::class, 'stats'])
            ->middleware('auth')
            ->name('.stats');

        Route::get('/sections/{section}', [KotodamaController::class, 'section'])
            ->middleware('auth')
            ->where('section', 'countdown|summary')
            ->name('.section');

        Route::get('/versus/{seed}', [KotodamaController::class, 'versus'])
            ->name('.versus');

        Route::get('/archive', [KotodamaController::class, 'archive'])
            ->middleware(['auth', 'user.is-pro-or-subscribed'])
            ->name('.archive');

        Route::get('/archive/{date}', [KotodamaController::class, 'playArchive'])
            ->middleware(['auth', 'user.is-pro-or-subscribed'])
            ->where('date', '\d{4}-\d{2}-\d{2}')
            ->name('.archive.play');
    });
