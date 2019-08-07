<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserNotifications extends KuroFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'notification'  => ['bail', 'required', 'string'],
            'read'          => ['bail', 'required', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
