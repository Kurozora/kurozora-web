<?php

namespace App\Helpers;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Config;

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

    private $success = true;
    private $errorMessage;
    private $errorCode;
    private $data = [];

    /**
     * Sets this JSON result to be an error, with a specified message
     *
     * @param string $message
     * @param int $errorCode
     * @return $this
     */
    public function setError($message = '', $errorCode = 0) {
        $this->success = false;
        $this->errorMessage = $message;
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * Sets the data for this JSON result. Only to be used when success.
     *
     * @param $dataArr
     * @return $this
     */
    public function setData($dataArr) {
        $this->data = $dataArr;
        return $this;
    }

    /**
     * Prints out the JSON result to the output feed.
     *
     * @param bool $doDie
     * @return $this
     */
    public function show($doDie = true) {
        header('Content-Type: application/json');

        $printArr = [
            'success'       => $this->success,
            'query_count'   => (int) Config::get(AppServiceProvider::$queryCountConfigKey)
        ];

        if(!$this->success && strlen($this->errorMessage)) {
            $printArr['error_message'] = $this->errorMessage;
            $printArr['error_code'] = $this->errorCode;
        }
        else {
            if(is_array($this->data))
                $printArr = array_merge($printArr, $this->data);
            else
                $printArr = array_merge($printArr, [$this->data]);
        }

        if($this->success)
            http_response_code(200);
        else
            http_response_code(400);

        echo json_encode($printArr);

        if($doDie) die;
        else return $this;
    }
}