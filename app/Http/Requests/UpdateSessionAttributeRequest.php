<?php

namespace App\Http\Requests;

use App\Rules\ValidateAPNDeviceToken;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionAttributeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'apn_device_token' => ['bail', new ValidateAPNDeviceToken]
        ];
    }
}
