<?php

namespace App\Exceptions;

use App\Helpers\JSONResult;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Validation\ValidationException;
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
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function render($request, Exception $exception)
    {
        // Custom render for unauthorized
        if($exception instanceof AuthorizationException) {
            return JSONResult::error('You are not permitted to do this.', [
                'error_code' => 9028123
            ]);
        }
        // Custom render for maintenance mode
        else if($exception instanceof MaintenanceModeException) {
            return JSONResult::error($exception->getMessage(), [
                'error_code' => 17281627
            ]);
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return JSONResult::error($exception->validator->errors()->first(), [
            'error_code' => 567
        ]);
    }
}
