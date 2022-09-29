<?php

namespace App\Providers;

use App;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after sign in.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            if (App::isLocal()) {
                Route::prefix('api')
                    ->middleware(['api'])
                    ->group(base_path('routes/api.php'));
            } else {
                Route::domain('api.' . config('app.domain'))
                    ->middleware(['api'])
                    ->group(base_path('routes/api.php'));
            }

            Route::domain(config('app.domain'))
                ->middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $method = $request->method();

            return match ($method) {
                'GET' => Limit::perMinutes(5, 2000)->by($method . ':' . $request->user()?->id ?: $request->ip()),
                default => Limit::perMinute(60)->by($method . ':' . ($request->user()?->id ?: $request->ip())),
            };
        });
    }
}
