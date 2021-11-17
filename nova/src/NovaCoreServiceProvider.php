<?php

namespace Laravel\Nova;

use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Contracts\QueryBuilder;
use Laravel\Nova\Http\Middleware\ServeNova;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Listeners\BootNova;
use Laravel\Nova\Query\Builder;
use Laravel\Octane\Events\RequestReceived;
use Spatie\Once\Cache;

/**
 * The primary purpose of this service provider is to push the ServeNova
 * middleware onto the middleware stack so we only need to register a
 * minimum number of resources for all other incoming app requests.
 */
class NovaCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::booted(BootNova::class);

        if ($this->app->runningInConsole()) {
            $this->app->register(NovaServiceProvider::class);
        }

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/nova.php', 'nova');
        }

        Route::middlewareGroup('nova', config('nova.middleware', []));

        $this->app->make(HttpKernel::class)
                    ->pushMiddleware(ServeNova::class);

        $this->app->afterResolving(NovaRequest::class, function ($request, $app) {
            if (! $app->bound(NovaRequest::class)) {
                $app->instance(NovaRequest::class, $request);
            }
        });

        tap($this->app['events'], function ($event) {
            $event->listen(RequestReceived::class, function ($event) {
                Nova::flushState();
                Cache::getInstance()->flush();
            });

            $event->listen(RequestHandled::class, function ($event) {
                Container::getInstance()->forgetInstance(NovaRequest::class);
            });
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (! defined('NOVA_PATH')) {
            define('NOVA_PATH', realpath(__DIR__.'/../'));
        }

        $this->app->bind(QueryBuilder::class, function ($app, $parameters) {
            return new Builder(...$parameters);
        });
    }
}
