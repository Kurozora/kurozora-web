<?php

namespace App\Http\Requests;

use App\Models\Language;
use App\Rules\ValidateBannerImage;
use App\Rules\ValidateProfileImage;
use App\Rules\ValidateTVRating;
use App\Rules\ValidateUserBiography;
use App\Rules\ValidateUsername;
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
            'username' => ['bail', new ValidateUserSlug],
            'nickname' => ['bail', new ValidateUsername],
            'profileImage' => ['bail', new ValidateProfileImage],
            'bannerImage' => ['bail', new ValidateBannerImage],
            'biography' => ['bail', new ValidateUserBiography],
            'preferredLanguage' => ['bail', 'string', 'exists:' . Language::TABLE_NAME . ',code'],
            'preferredTVRating' => ['bail', new ValidateTVRating],
            'preferredTimezone' => ['bail', 'timezone:all'],
        ];
    }
}
