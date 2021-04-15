<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\EmailVerificationNotificationController;
use App\Http\Controllers\Web\EmailVerificationPromptController;
use App\Http\Controllers\Web\NewPasswordController;
use App\Http\Controllers\Web\PasswordResetLinkController;
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

Route::name('verification')
    ->group(function () {
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->middleware(['auth'])
            ->name('.notice');

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['auth', 'signed', 'throttle:6,1'])
            ->name('.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['auth', 'throttle:6,1'])
            ->name('.send');
    });

Route::name('password')
    ->group(function () {
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->middleware(['guest'])
            ->name('.request');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware(['guest'])
            ->name('.email');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->middleware('guest')
            ->name('.reset');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest'])
            ->name('.update');
    });
