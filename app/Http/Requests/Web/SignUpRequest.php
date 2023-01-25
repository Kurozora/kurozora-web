<?php

namespace App\Http\Requests\Web;

use App\Rules\ValidateNickname;
use App\Rules\ValidateProfileImage;
use App\Rules\ValidateEmail;
use App\Rules\ValidatePassword;
use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
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
            'username'      => ['bail', 'required_without:nickname', new ValidateNickname],
            'nickname'      => ['bail', 'required_without:username', new ValidateNickname],
            'password'      => ['bail', 'required', new ValidatePassword],
            'email'         => ['bail', 'required', new ValidateEmail(['must-be-available' => true])],
            'profileImage'  => ['bail', new ValidateProfileImage],
        ];
    }
}
