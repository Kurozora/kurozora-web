<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use FG\ASN1\Exception\NotImplementedException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class APIController extends Controller
{
    /**
     * The index page of the API.
     *
     * @return Application|Redirector|RedirectResponse
     */
    function index(): Application|Redirector|RedirectResponse
    {
        return redirect(route('api'));
    }

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
     * Returns a plain JSON response for the API.
     *
     * @return JsonResponse
     */
    function settings(): JsonResponse
    {
        return JSONResult::success([
            'data' => SettingsResource::make([])
        ]);
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
