<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserNotificationsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'notification'  => ['bail', 'required', 'string'],
            'read'          => ['bail', 'required', 'numeric', 'in:0,1'],
        ];
    }
}
