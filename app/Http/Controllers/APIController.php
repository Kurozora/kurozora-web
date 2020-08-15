<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use FG\ASN1\Exception\NotImplementedException;
use Illuminate\Http\JsonResponse;

class APIController extends Controller
{
    /**
     * Returns a plain JSON response for the API.
     *
     * @return JsonResponse
     */
    function info(): JsonResponse
    {
        return JSONResult::success();
    }

    /**
     * Returns the error response for the API.
     *
     * @throws NotImplementedException
     */
    function error(): void
    {
        throw new NotImplementedException('Endpoint is currently unavailable and reserved for future use.');
    }
}
