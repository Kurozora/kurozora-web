<?php

namespace App\Http\Requests;

use App\Rules\ValidateEmail;
use Illuminate\Foundation\Http\FormRequest;

class SIWARegistration extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'     => ['bail', 'required', new ValidateEmail(true)],
            'siwa_id'   => ['bail', 'required', 'string', 'min:1', 'max:200']
        ];
    }
}
