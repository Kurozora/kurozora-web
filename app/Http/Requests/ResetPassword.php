<?php

namespace App\Http\Requests;

use App\Rules\ValidateEmail;

class ResetPassword extends KuroFormRequest
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
            'email' => ['bail', 'required', new ValidateEmail(false)],
        ];
    }
}
