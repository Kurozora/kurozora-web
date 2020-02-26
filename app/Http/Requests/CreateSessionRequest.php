<?php

namespace App\Http\Requests;

use App\Rules\ValidateAPNDeviceToken;
use App\Rules\ValidateEmail;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'             => ['bail', 'required', new ValidateEmail(['must-be-taken' => true])],
            'password'          => ['bail', 'required'],
            'device'            => ['bail', 'required', 'max:255']
        ];
    }
}
