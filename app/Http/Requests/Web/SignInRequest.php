<?php

namespace App\Http\Requests\Web;

use App\Rules\ValidateEmail;
use Illuminate\Foundation\Http\FormRequest;

class SignInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'     => ['required', new ValidateEmail()],
            'password'  => ['required', 'string'],
        ];
    }
}
