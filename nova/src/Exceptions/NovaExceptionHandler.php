<?php

namespace Laravel\Nova\Exceptions;

use Closure;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Nova\Nova;

class NovaExceptionHandler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     *
     * Used only on Laravel 8 and above.
     *
     * @return void
     */
    public function register()
    {
        with(Nova::$reportCallback, function ($handler) {
            if (is_callable($handler) || $handler instanceof Closure) {
                $this->reportable(function (\Throwable $e) use ($handler) {
                    call_user_func($handler, $e);
                })->stop();
            }
        });

        Nova::$reportCallback = null;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Throwable
     */
    public function report(\Throwable $e)
    {
        return with(Nova::$reportCallback, function ($handler) use ($e) {
            if (is_callable($handler) || $handler instanceof Closure) {
                return call_user_func($handler, $e);
            }

            return parent::report($e);
        });
    }
}
