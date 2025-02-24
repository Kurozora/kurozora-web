<?php

namespace App\Helpers;

use App\Http\Resources\JSONErrorResource;
use App\Models\APIError;
use App\Providers\AppServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class JSONResult
{
    /**
     * Returns an error response to the client.
     *
     * @param APIError[] $apiError
     *
     * @return JsonResponse
     */
    static function error(array $apiError): JsonResponse
    {
        $endResponse = array_merge(self::getDefaultResponseArray(), [
            'errors' => JSONErrorResource::collection($apiError)
        ]);

        $statusCode = null;
        if (count($apiError) == 1) {
            $statusCode = $apiError[0]->status;
        }

        return Response::json($endResponse, $statusCode ?? 400);
    }

    /**
     * Returns a successful response to the client.
     *
     * @param array $data
     * @param bool  $includeMetaData
     *
     * @return JsonResponse
     */
    static function success(array $data = [], bool $includeMetaData = true): JsonResponse
    {
        if (!$includeMetaData) {
            return Response::json($data);
        }

        $endResponse = array_merge(self::getDefaultResponseArray(), $data);

        return Response::json($endResponse);
    }

    /**
     * Returns the default array that will be included in every JSON response.
     *
     * @return array
     */
    private static function getDefaultResponseArray(): array
    {
        $meta = [
            'version' => config('app.version'),
            'minimumAppVersion' => config('app.ios.version'),
            'isMaintenanceModeEnabled' => app()->isDownForMaintenance()
        ];

        if (app()->isLocal()) {
            $meta['isUserAuthenticated'] = auth()->check();
            $meta['authenticatedUserID'] = (string) auth()->id();
            $meta['queryCount'] = (int) config(AppServiceProvider::$queryCountConfigKey);
        }

        return [
            'meta' => $meta
        ];
    }
}
