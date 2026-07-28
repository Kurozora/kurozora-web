<?php

use App\Http\Controllers\API\v1\Minigames\KotodamaController;

Route::prefix('/kotodama')
    ->name('.kotodama')
    ->group(function () {
        Route::get('/daily', [KotodamaController::class, 'daily'])
            ->middleware('auth.kurozora')
            ->name('.daily');

        Route::get('/unlimited', [KotodamaController::class, 'unlimited'])
            ->middleware('auth.kurozora:optional')
            ->name('.unlimited');

        Route::get('/archive/{date}', [KotodamaController::class, 'archive'])
            ->middleware(['auth.kurozora', 'user.is-pro-or-subscribed'])
            ->name('.archive');

        Route::prefix('/versus')
            ->name('.versus')
            ->group(function () {
                Route::post('/', [KotodamaController::class, 'createVersus'])
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->name('.create');

                Route::get('/{seed}', [KotodamaController::class, 'joinVersus'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.join');
            });

        Route::prefix('/games/{game}')
            ->name('.games')
            ->group(function () {
                Route::post('/guess', [KotodamaController::class, 'guess'])
                    ->middleware(['auth.kurozora:optional', 'user.not-timed-out'])
                    ->name('.guess');

                Route::post('/abandon', [KotodamaController::class, 'abandon'])
                    ->middleware(['auth.kurozora:optional', 'user.not-timed-out'])
                    ->name('.abandon');

                Route::get('/share', [KotodamaController::class, 'share'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.share');
            });

        Route::prefix('/leaderboards')
            ->name('.leaderboards')
            ->group(function () {
                Route::get('/daily/{date?}', [KotodamaController::class, 'dailyLeaderboard'])
                    ->name('.daily');

                Route::get('/streak', [KotodamaController::class, 'streakLeaderboard'])
                    ->name('.streak');
            });

        Route::prefix('/me')
            ->name('.me')
            ->group(function () {
                Route::get('/stats', [KotodamaController::class, 'myStats'])
                    ->middleware('auth.kurozora')
                    ->name('.stats');
            });
    });
