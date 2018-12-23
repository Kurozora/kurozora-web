<?php

namespace App\Exceptions;

use App\Helpers\KuroMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // Page not found exception
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class
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
        // Send an email to admin(s)
        $adminEmail = config('app.admin_email');

        if($adminEmail != null) {
            // Get date
            $curDate = Carbon::now();
            $formattedDate = $curDate->format('d-m-Y H:i');

            // Format subject
            $subject = '[exception:' . $formattedDate . '] ' . get_class($exception);

            // Send the mail
            (new KuroMail())
                ->setTo($adminEmail)
                ->setSubject($subject)
                ->setContent(view('email.admin_exception_email', ['exception' => $exception])->render())
                ->send();
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
        return parent::render($request, $exception);
    }
}
