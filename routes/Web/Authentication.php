<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\EmailVerificationNotificationController;
use App\Http\Controllers\Web\EmailVerificationPromptController;
use App\Http\Controllers\Web\NewPasswordController;
use App\Http\Controllers\Web\PasswordResetLinkController;
use App\Http\Controllers\Web\RecoveryCodeController;
use App\Http\Controllers\Web\SignUpUserController;
use App\Http\Controllers\Web\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\Web\TwoFactorAuthenticationController;
use App\Http\Controllers\Web\TwoFactorQrCodeController;
use App\Http\Controllers\Web\VerifyEmailController;

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
            ->middleware(['guest'])
            ->name('.reset');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest'])
            ->name('.update');
    });

Route::name('two-factor')
    ->group(function () {
        Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
            ->middleware(['guest'])
            ->name('.sign-in');

        Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(['guest'])
            ->name('.update');

        Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware(['auth', 'password.confirm'])
            ->name('.two-factor-authentication.store');

        Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware(['auth', 'password.confirm'])
            ->name('.two-factor-authentication.remove');

        Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware(['auth', 'password.confirm'])
            ->name('.two-factor-qr-code');

        Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware(['auth', 'password.confirm'])
            ->name('.two-factor-recovery-codes');

        Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware(['auth', 'password.confirm'])
            ->name('.two-factor-recovery-codes.store');
    });
