<?php

namespace App\Http\Requests;

use App\Rules\ValidateEmail;
use App\Models\Session;
use Illuminate\Foundation\Http\FormRequest;

class CreateSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge([
            'email'             => ['bail', 'required', new ValidateEmail(['must-be-taken' => true])],
            'password'          => ['bail', 'required']
        ], Session::platformRules());
    }
}
