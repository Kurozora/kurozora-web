<?php

namespace App\Exceptions;

use App\Helpers\JSONResult;
use App\Models\APIError;
use FG\ASN1\Exception\NotImplementedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Swift_TransportException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // Page not found exception
        NotFoundHttpException::class,
        // PHP artisan command not found exception
        CommandNotFoundException::class,
        // Access denied exception
        AccessDeniedHttpException::class,
        // Missing arguments exception
        RuntimeException::class,
        // Swift Mailer too many mails exception
        Swift_TransportException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if (str($request->root())->startsWith($request->getScheme().'://api.')) {
            return $this->renderAPI($request, $e);
        }

        // Log some info to catch the issue in production
        logger()->info($e->getMessage(), [
            'user' => auth()->user()?->username,
            'url' => $request->url(),
            'input' => $request->all(),
        ]);

        return parent::render($request, $e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     *
     * @throws Throwable
     */
    public function renderAPI(Request $request, Throwable $e): Response
    {
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
        else if ($e instanceof AuthorizationException) {
            $apiError = new APIError();
            $apiError->id = 40003;
            $apiError->status = 403;
            $apiError->title = 'Forbidden';
            $apiError->detail = $e->getMessage();
            return JSONResult::error([$apiError]);
        }
        // Custom render for model not found
        else if ($e instanceof ModelNotFoundException) {
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
            return $this->invalidJson($request, $e);
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
        }

        return parent::render($request, $e);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param Request $request
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        $apiErrors = [];
        $errors = $exception->validator->errors()->all();

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
}
