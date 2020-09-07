<?php

namespace App\Http\Requests;

use App\Rules\ValidateAvatarImage;
use App\Rules\ValidateBannerImage;
use App\Rules\ValidateUserBiography;
use App\Rules\ValidateUsername;
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
            'username'      => ['bail', new ValidateUsername],
            'profileImage'  => ['bail', new ValidateAvatarImage, 'nullable'],
            'bannerImage'   => ['bail', new ValidateBannerImage, 'nullable'],
            'biography'     => ['bail', new ValidateUserBiography, 'nullable']
        ];
    }
}
