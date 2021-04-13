<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\EmailVerificationNotificationController;
use App\Http\Controllers\Web\EmailVerificationPromptController;
use App\Http\Controllers\Web\VerifyEmailController;
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

Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware(['auth'])
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
