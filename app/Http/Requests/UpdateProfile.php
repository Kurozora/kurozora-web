<?php

namespace App\Http\Requests;

use App\Rules\ValidateAvatarImage;
use App\Rules\ValidateBannerImage;
use App\Rules\ValidateUserBiography;

class UpdateProfile extends KuroFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'profileImage'  => ['bail', new ValidateAvatarImage],
            'bannerImage'   => ['bail', new ValidateBannerImage],
            'biography'     => ['bail', new ValidateUserBiography]
        ];
    }
}
