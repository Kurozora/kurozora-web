<?php

namespace App\Http\Requests;

use App\Rules\ValidateEmail;
use App\Rules\ValidatePassword;
use App\Rules\ValidateUsername;

class Registration extends KuroFormRequest
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
            'username'  => new ValidateUsername,
            'password'  => new ValidatePassword,
            'email'     => new ValidateEmail,
        ];
    }
}
