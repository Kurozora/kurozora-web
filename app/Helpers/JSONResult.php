<?php

namespace App\Helpers;

use App\Http\Resources\JSONErrorResource;
use App\Models\APIError;
use App\Providers\AppServiceProvider;
use Auth;
use Illuminate\Http\JsonResponse;
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
    static function error(array $apiError): JsonResponse
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
    static function success(array $data = []): JsonResponse
    {
        if (!is_array($data)) $data = [$data];

        $endResponse = array_merge(self::getDefaultResponseArray(), $data);

        return Response::json($endResponse, 200);
    }

    /**
     * Returns the default array that will be included in every JSON response.
     *
     * @return array
     */
    private static function getDefaultResponseArray(): array
    {
        $meta = [
            'version'               => Config::get('app.version'),
            'isUserAuthenticated'   => Auth::check(),
            'authenticatedUserID'   => Auth::id()
        ];

        if (app()->environment('local')) {
            $meta['queryCount'] = (int) Config::get(AppServiceProvider::$queryCountConfigKey);
        }

        return [
            'meta' => $meta
        ];
    }
}
