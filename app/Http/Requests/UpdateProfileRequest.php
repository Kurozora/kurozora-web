<?php

namespace App\Http\Requests;

use App\Rules\ValidateBannerImage;
use App\Rules\ValidateNickname;
use App\Rules\ValidateProfileImage;
use App\Rules\ValidateUserBiography;
use App\Rules\ValidateUserSlug;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'username'      => ['bail', new ValidateUserSlug],
            'nickname'      => ['bail', new ValidateNickname],
            'profileImage'  => ['bail', new ValidateProfileImage],
            'bannerImage'   => ['bail', new ValidateBannerImage],
            'biography'     => ['bail', new ValidateUserBiography]
        ];
    }
}
