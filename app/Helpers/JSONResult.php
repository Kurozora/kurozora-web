<?php

namespace App\Helpers;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Config;

class JSONResult {
    // Error messages
    const ERROR_SESSION_REJECTED = 'The server rejected your session. Please restart the app to solve this issue.';
    const ERROR_CANNOT_POST_IN_THREAD = 'You cannot post in this thread.';
    const ERROR_FORUM_SECTION_NON_EXISTENT = 'The specified forum section is not recognized.';
    const ERROR_FORUM_THREAD_NON_EXISTENT = 'The specified thread was not found.';
    const ERROR_ANIME_NON_EXISTENT = 'The specified anime was not found.';

    private $success = true;
    private $errorMessage;
    private $data = [];

    /**
     * Sets this JSON result to be an error, with a specified message
     *
     * @param string $message
     * @return $this
     */
    public function setError($message = '') {
        $this->success = false;
        $this->errorMessage = $message;
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
            'query_count'   => Config::get(AppServiceProvider::$queryCountConfigKey)
        ];

        if(!$this->success && strlen($this->errorMessage))
            $printArr['error_message'] = $this->errorMessage;
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