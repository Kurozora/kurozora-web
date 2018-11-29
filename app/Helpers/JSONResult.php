<?php

namespace App\Helpers;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Config;

class JSONResult {
    // Error messages
    const ERROR_SESSION_REJECTED = 'The server rejected your session. Please restart the app to solve this issue.';

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

        echo json_encode($printArr);

        if($doDie) die;
        else return $this;
    }
}