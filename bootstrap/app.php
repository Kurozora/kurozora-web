<?php

use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\CheckKurozoraUserAuthentication;
use App\Http\Middleware\EnsureAPIRequestsAreStateful;
use App\Http\Middleware\ExploreCategoryAlwaysEnabled;
use App\Http\Middleware\HttpAccept;
use App\Http\Middleware\HttpContentSecurityPolicy;
use App\Http\Middleware\KAuthenticate;
use App\Http\Middleware\Localization;
use App\Http\Middleware\NoSessionForBotsMiddleware;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\Timezone;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\TVRating;
use App\Http\Middleware\UserIsProOrSubscribed;
use App\Http\Middleware\ValidateCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            if (app()->isLocal()) {
                Route::prefix('api')
                    ->middleware(['api'])
                    ->group(base_path('routes/api.php'));

                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            } else {
                Route::domain('api.' . config('app.domain'))
                    ->middleware(['api'])
                    ->group(base_path('routes/api.php'));

                Route::domain(config('app.domain'))
                    ->middleware('web')
                    ->group(base_path('routes/web.php'));
            }
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->use([
                // \App\Http\Middleware\TrustHosts::class,
                TrustProxies::class,
                HandleCors::class,
                PreventRequestsDuringMaintenance::class,
                ValidatePostSize::class,
                TrimStrings::class,
                ConvertEmptyStringsToNull::class,
            ])
            ->web([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                NoSessionForBotsMiddleware::class,
                StartSession::class,
                'auth.session',
                Localization::class,
                Timezone::class,
                TVRating::class,
                ShareErrorsFromSession::class,
                ValidateCsrfToken::class,
                SubstituteBindings::class,
            ])
            ->api([
                EnsureAPIRequestsAreStateful::class,
                ThrottleRequests::class . ':api',
                Localization::class,
                Timezone::class,
                TVRating::class,
                SubstituteBindings::class,
            ])
            ->alias([
                'auth' => KAuthenticate::class,
                'auth.basic' => AuthenticateWithBasicAuth::class,
                'auth.kurozora' => CheckKurozoraUserAuthentication::class,
                'auth.session' => AuthenticateSession::class,
                'cache.headers' => SetCacheHeaders::class,
                'can' => Authorize::class,
                'guest' => RedirectIfAuthenticated::class,
                'headers.http-accept' => HttpAccept::class,
                'headers.http-csp' => HttpContentSecurityPolicy::class,
                'password.confirm' => RequirePassword::class,
                'signed' => ValidateSignature::class,
                'throttle' => ThrottleRequests::class,
                'user.is-pro-or-subscribed' => UserIsProOrSubscribed::class,
                'verified' => EnsureEmailIsVerified::class,
                'explore.always-enabled' => ExploreCategoryAlwaysEnabled::class
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
