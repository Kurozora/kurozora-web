<?php

namespace App\Http\Requests;

use App\Rules\ValidateAPNDeviceToken;
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
            'username'          => ['bail', 'required', 'exists:'. User::TABLE_NAME . ',username'],
            'password'          => ['bail', 'required'],
            'device'            => ['bail', 'required', 'max:255'],
            'apn_device_token'  => ['bail', new ValidateAPNDeviceToken]
        ];
    }
}
