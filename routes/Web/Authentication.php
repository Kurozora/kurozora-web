<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;

Route::get('/sign-in', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest'])
    ->name('sign-in');

Route::post('/sign-in', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1']);

Route::post('/sign-out', [AuthenticatedSessionController::class, 'destroy'])
    ->name('sign-out');
