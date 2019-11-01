<?php

namespace App\Http\Requests;

use App\Rules\ValidateAvatarImage;
use App\Rules\ValidateEmail;
use App\Rules\ValidatePassword;
use App\Rules\ValidateUsername;

class Registration extends KuroFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username'      => ['bail', 'required', new ValidateUsername],
            'password'      => ['bail', 'required', new ValidatePassword],
            'email'         => ['bail', 'required', new ValidateEmail(true)],
            'profileImage'  => ['bail', new ValidateAvatarImage],
        ];
    }
}
