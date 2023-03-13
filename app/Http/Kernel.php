<?php

namespace App\Http;

use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\CheckKurozoraUserAuthentication;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureAPIRequestsAreStateful;
use App\Http\Middleware\ExploreCategoryAlwaysEnabled;
use App\Http\Middleware\HttpAccept;
use App\Http\Middleware\HttpContentSecurityPolicy;
use App\Http\Middleware\KAuthenticate;
use App\Http\Middleware\Localization;
use App\Http\Middleware\NoSessionForBotsMiddleware;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\UserIsProOrSubscribed;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
       // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            NoSessionForBotsMiddleware::class,
            StartSession::class,
            'auth.session',
            'localization',
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            EnsureAPIRequestsAreStateful::class,
            ThrottleRequests::class.':api',
            'localization',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => KAuthenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.kurozora' => CheckKurozoraUserAuthentication::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'headers.http-accept' => HttpAccept::class,
        'headers.http-csp' => HttpContentSecurityPolicy::class,
        'localization' => Localization::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'user.is-pro-or-subscribed' => UserIsProOrSubscribed::class,
        'verified' => EnsureEmailIsVerified::class,
        'explore.always-enabled' => ExploreCategoryAlwaysEnabled::class
    ];
}
