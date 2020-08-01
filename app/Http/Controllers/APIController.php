<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
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
}
