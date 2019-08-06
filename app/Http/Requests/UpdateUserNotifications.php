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
            'status'        => ['bail', 'required', 'string', 'in:read,unread'],
        ];
    }
}
