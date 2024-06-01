<?php

use App\Helpers\JSONResult;
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
use App\Models\APIError;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\ViewException;
use Nette\NotImplementedException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
            ->redirectGuestsTo(function() {
                return route('sign-in');
            })
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
            ])
            ->priority([
                HandlePrecognitiveRequests::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                EnsureAPIRequestsAreStateful::class,
                StartSession::class,
                'auth.session',
                Localization::class,
                Timezone::class,
                TVRating::class,
                ShareErrorsFromSession::class,
                AuthenticatesRequests::class,
                ThrottleRequests::class,
                ThrottleRequestsWithRedis::class,
                AuthenticatesSessions::class,
                SubstituteBindings::class,
                Authorize::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions
            ->dontReport([
                // Page not found exception
                NotFoundHttpException::class,
                // PHP artisan command not found exception
                CommandNotFoundException::class,
                // Access denied exception
                AccessDeniedHttpException::class,
                // Missing arguments exception
                RuntimeException::class
            ])
            ->dontFlash([
                'current_password',
                'password',
                'password_confirmation',
            ])
            ->render(function (Throwable $e, $request) {
                $renderAPIException = app()->isLocal() ?
                    str($request->url())->contains('api') :
                    str($request->root())->startsWith($request->getScheme() . '://api.');

                if ($renderAPIException) {
                    // Custom render for authentication
                    if ($e instanceof AuthenticationException) {
                        $apiError = new APIError();
                        $apiError->id = 40001;
                        $apiError->status = 401;
                        $apiError->title = 'Unauthorized';
                        $apiError->detail = $e->getMessage();
                        return JSONResult::error([$apiError]);
                    }
                    // Custom render for unauthorized
                    else if ($e instanceof AuthorizationException || $e->getPrevious() instanceof AuthorizationException) {
                        $apiError = new APIError();
                        $apiError->id = 40003;
                        $apiError->status = 403;
                        $apiError->title = 'Forbidden';
                        $apiError->detail = $e->getMessage();
                        return JSONResult::error([$apiError]);
                    }
                    // Custom render for model not found
                    else if ($e instanceof ModelNotFoundException) {
                        // Log some info to catch the issue in production
                        logger()->debug($e->getMessage(), [
                            'user' => auth()->user()?->username,
                            'url' => $request->url(),
                            'input' => $request->all(),
                        ]);

                        $apiError = new APIError();
                        $apiError->id = 40004;
                        $apiError->status = 404;
                        $apiError->title = 'Not Found';
                        $apiError->detail = 'The requested resource doesn’t exist.';
                        return JSONResult::error([$apiError]);
                    }
                    // Custom render for conflict
                    else if ($e instanceof ConflictHttpException) {
                        $apiError = new APIError();
                        $apiError->id = 40009;
                        $apiError->status = 409;
                        $apiError->title = 'Conflict';
                        $apiError->detail = $e->getMessage();
                        return JSONResult::error([$apiError]);
                    }
                    // Custom render for validation
                    else if ($e instanceof ValidationException) {
                        $apiErrors = [];
                        $errors = $e->validator->errors()->all();

                        foreach ($errors as $error) {
                            $apiError = new APIError();
                            $apiError->id = 40022;
                            $apiError->status = 422;
                            $apiError->title = 'Unprocessable Entity';
                            $apiError->detail = $error;
                            $apiErrors[] = $apiError;
                        }

                        return JSONResult::error($apiErrors);
                    }
                    // Custom render for too many request
                    else if ($e instanceof TooManyRequestsHttpException) {
                        $apiError = new APIError();
                        $apiError->id = 40029;
                        $apiError->status = 429;
                        $apiError->title = 'Too Many Requests';
                        $apiError->detail = $e->getMessage();
                        return JSONResult::error([$apiError]);
                    }
                    // Custom render for not implemented
                    else if ($e instanceof NotImplementedException) {
                        // Log some info to catch the issue in production
                        logger()->debug($e->getMessage(), [
                            'user' => auth()->user()?->username,
                            'url' => $request->url(),
                            'input' => $request->all(),
                        ]);

                        $apiError = new APIError();
                        $apiError->id = 50001;
                        $apiError->status = 501;
                        $apiError->title = 'Not Implemented';
                        $apiError->detail = $e->getMessage();
                        return JSONResult::error([$apiError]);
                    }
                    // Custom render for service unavailable
                    else if ($e instanceof ServiceUnavailableHttpException) {
                        $apiError = new APIError();
                        $apiError->id = 50003;
                        $apiError->status = 503;
                        $apiError->title = 'The service is currently unavailable to process requests.';
                        $apiError->detail = $e->getMessage();
                        return JSONResult::error([$apiError]);
                    } else if (app()->isDownForMaintenance()) {
                        $apiError = new APIError();
                        $apiError->id = 50003;
                        $apiError->status = 503;
                        $apiError->title = __('Scheduled Maintenance');
                        $apiError->detail = __('Kurozora is currently under maintenance. All services will be available shortly. If this continues for more than an hour, you can follow the status on Twitter.');
                        return JSONResult::error([$apiError]);
                    }
                }

                if ($e instanceof ViewException) {
                    // Log some info to catch the issue in production
                    logger()->debug($e->getMessage(), [
                        'user' => auth()->user()?->username,
                        'url' => $request->url(),
                        'input' => $request->all(),
                    ]);
                } else if (str($e->getTraceAsString())->contains('ServerNotificationController') && !$request->has('provider')) {
                    logger()->channel('stack')->critical(print_r($request->all(), true));
                    Http::post(route('liap.serverNotifications', ['provider' => 'app-store']), $request->all());
                } else if ($request->routeIs('liap.serverNotifications')) {
                    logger()->channel('stack')->critical(print_r($request->all(), true));
                }

                return null;
            });
    })
    ->create();
