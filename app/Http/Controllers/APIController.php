<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;

class APIController extends Controller
{
    /**
     * Returns a plain JSON response for the API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    function info() {
        return JSONResult::success();
    }
}
