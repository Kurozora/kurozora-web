<?php

namespace App\Http\Requests;

use App\Rules\ValidateAvatarImage;
use App\Rules\ValidateEmail;
use App\Rules\ValidatePassword;
use App\Rules\ValidateUsername;
use Illuminate\Foundation\Http\FormRequest;

class Registration extends FormRequest
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
            'email'         => ['bail', 'required', new ValidateEmail(['must-be-available' => true])],
            'profileImage'  => ['bail', new ValidateAvatarImage],
        ];
    }
}
