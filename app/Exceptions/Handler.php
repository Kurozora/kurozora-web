<?php

namespace App\Exceptions;

use App\Helpers\JSONResult;
use App\Jobs\SendAdminExceptionMail;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Swift_TransportException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // Page not found exception
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        // PHP artisan command not found exception
        \Symfony\Component\Console\Exception\CommandNotFoundException::class,
        // Access denied exception
        \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
        // Missing arguments exception
        \Symfony\Component\Console\Exception\RuntimeException::class,
        // Swift Mailer too many mails exception
        Swift_TransportException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     * @throws \Throwable
     */
    public function report(Exception $exception)
    {
        if($this->shouldReport($exception)) {
            // Dispatch job to send admin email
            SendAdminExceptionMail::dispatch($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // Custom render for unauthorized
        if($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            (new JSONResult())->setError(JSONResult::ERROR_NOT_PERMITTED, 9028123)->show();
        }
        // Custom render for maintenance mode
        else if($exception instanceof \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException) {
            (new JSONResult())->setError($exception->getMessage(), 17281627)->show();
        }

        return parent::render($request, $exception);
    }
}
