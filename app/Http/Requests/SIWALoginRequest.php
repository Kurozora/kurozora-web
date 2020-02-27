<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SIWALoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'identity_token' => ['required', 'string']
        ];
    }
}
