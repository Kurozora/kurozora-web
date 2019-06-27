<?php

namespace App\Helpers;

use App\Providers\AppServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class JSONResult {
    // Error messages
    const ERROR_NOT_PERMITTED = 'You are not permitted to do this.';
    const ERROR_SESSION_REJECTED = 'The server rejected your session. Please restart the app to solve this issue.';
    const ERROR_CANNOT_POST_IN_THREAD = 'You cannot post in this thread.';
    const ERROR_FORUM_SECTION_NON_EXISTENT = 'The specified forum section is not recognized.';
    const ERROR_FORUM_THREAD_NON_EXISTENT = 'The specified thread was not found.';
    const ERROR_FORUM_REPLY_NON_EXISTENT = 'The specified reply was not found.';
    const ERROR_ANIME_NON_EXISTENT = 'The specified anime was not found.';
    const ERROR_ANIME_SEASON_NON_EXISTENT = 'The specified season was not found.';
    const ERROR_ANIME_EPISODE_NON_EXISTENT = 'The specified episode was not found.';
    const ERROR_NOTIFICATION_EXISTENT = 'The specified notification was not found.';

    /**
     * Returns an error response to the client.
     *
     * @param string $message
     * @param int $errorCode
     * @return JsonResponse
     */
    static function error($message = 'Something went wrong with your request.', $errorCode = 0) {
        $endResponse = array_merge(self::getDefaultResponseArray(false), [
            'error_message' => $message,
            'error_code'    => $errorCode
        ]);

        return Response::json($endResponse, 400);
    }

    /**
     * Returns a successful response to the client.
     *
     * @param array $data
     * @return JsonResponse
     */
    static function success($data = []) {
        if(is_array($data)) $data = [$data];

        $endResponse = array_merge(self::getDefaultResponseArray(true), $data);

        return Response::json($endResponse, 200);
    }

    /**
     * Returns the default array that will be included in every JSON response.
     *
     * @param $isSuccess
     * @return array
     */
    private static function getDefaultResponseArray($isSuccess) {
        return [
            'success'       => (bool) $isSuccess,
            'query_count'   => (int) Config::get(AppServiceProvider::$queryCountConfigKey),
            'version'       => Config::get('app.version')
        ];
    }
}