<?php

namespace App\Http\Requests;

use App\Rules\ValidateThreadContent;
use App\Rules\ValidateThreadTitle;

class PostThread extends KuroFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'     => ['bail', 'required', new ValidateThreadTitle],
            'content'   => ['bail', 'required', new ValidateThreadContent],
        ];
    }
}
