<?php

namespace App\Http\Requests;

use App\Models\Session;
use App\Rules\ValidateEmail;
use Illuminate\Foundation\Http\FormRequest;

class CreateSessionAttributeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge([
            'email'     => ['bail', 'required', new ValidateEmail],
            'password'  => ['bail', 'required']
        ], Session::platformRules());
    }
}
