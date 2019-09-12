<?php

namespace App\Http\Requests;

use App\Helpers\JSONResult;
use Illuminate\Foundation\Http\FormRequest;

class KuroFormRequest extends FormRequest
{
    /**
     * Formats the validation errors properly
     *
     * @param $validator
     * @return \Illuminate\Http\JsonResponse
     */
    public function withValidator($validator)
    {
        // Return our custom JSON result when an error occurs
        if($validator->fails())
            return JSONResult::error($validator->errors()->first(), 567)->send();
    }
}
