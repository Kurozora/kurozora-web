<?php

namespace App\Http\Requests;

use App\Rules\ValidateAPNDeviceToken;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'apn_device_token' => ['bail', new ValidateAPNDeviceToken]
        ];
    }
}
