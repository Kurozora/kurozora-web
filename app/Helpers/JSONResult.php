<?php

namespace App\Helpers;

use App\Models\APIError;
use App\Http\Resources\JSONErrorResource;
use App\Providers\AppServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class JSONResult
{
    /**
     * Returns an error response to the client.
     *
     * @param APIError[] $apiError
     * @return JsonResponse
     */
    static function error(array $apiError)
    {
        $endResponse = array_merge(self::getDefaultResponseArray(), [
            'errors' => JSONErrorResource::collection($apiError)
        ]);

        $statusCode = null;
        if (count($apiError) == 1)
            $statusCode = $apiError[0]->status;

        return Response::json($endResponse, $statusCode ?? 400);
    }

    /**
     * Returns a successful response to the client.
     *
     * @param array $data
     * @return JsonResponse
     */
    static function success($data = [])
    {
        if(!is_array($data)) $data = [$data];

        $endResponse = array_merge(self::getDefaultResponseArray(), $data);

        return Response::json($endResponse, 200);
    }

    /**
     * Returns the default array that will be included in every JSON response.
     *
     * @return array
     */
    private static function getDefaultResponseArray()
    {
        return [
            'meta' => [
                'version'               => Config::get('app.version'),
                'queryCount'            => (int) Config::get(AppServiceProvider::$queryCountConfigKey),
                'isUserAuthenticated'   => Auth::check(),
                'authenticatedUserID'   => Auth::id()
            ]
        ];
    }
}
