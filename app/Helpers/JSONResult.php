<?php

/**
 * @author Musa Semou <mussesemou99@gmail.com>
 */

namespace App\Helpers;

/**
    Class to generate and show/echo a JSON response
**/
class JSONResult {
    private $success = true;
    private $errorMessage;
    private $data = [];

    /**
        Sets this JSON result to be an error, with a specified message
    **/
    public function setError($message = '') {
        $this->success = false;
        $this->errorMessage = $message;
        return $this;
    }

    /**
        Sets the data for this JSON result. Only to be used when success.
    **/
    public function setData($dataArr) {
        $this->data = $dataArr;
        return $this;
    }

    /**
        Prints out the JSON result to the output feed.
    **/
    public function show($doDie = true) {
        header('Content-Type: application/json');

        $printArr = ['success' => $this->success];

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