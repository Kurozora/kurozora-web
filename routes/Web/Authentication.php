<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\SignUpUserController;

Route::get('/sign-in', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest'])
    ->name('sign-in');

Route::post('/sign-in', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1']);

Route::post('/sign-out', [AuthenticatedSessionController::class, 'destroy'])
    ->name('sign-out');

Route::get('/sign-up', [SignUpUserController::class, 'create'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('sign-up');

Route::post('/sign-up', [SignUpUserController::class, 'store'])
    ->middleware(['guest']);
