<?php

namespace App\Helpers;

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
     * @param string $message
     * @param array $info
     * @return JsonResponse
     */
    static function error($message = 'Something went wrong with your request.', $info = [])
    {
        $endResponse = array_merge(self::getDefaultResponseArray(false), [
            'error_message' => $message,
            'error_code'    => (isset($info['error_code'])) ? $info['error_code'] : null
        ]);

        return Response::json($endResponse, (isset($info['status_code'])) ? $info['status_code'] : 400);
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
