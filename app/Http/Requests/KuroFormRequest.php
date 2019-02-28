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
     */
    public function withValidator($validator)
    {
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();
    }
}
