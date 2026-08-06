<?php

namespace App\Http\Requests;

use App\Enums\DiscordActivityName;
use App\Enums\DiscordPresenceImage;
use App\Enums\RatingStyle;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'scrobbleThreshold' => ['bail', 'integer', 'min:80', 'max:100'],
            'discordRichPresenceEnabled' => ['bail', 'boolean'],
            'discordPresenceImage' => ['bail', 'integer', 'in:' . implode(',', DiscordPresenceImage::getValues())],
            'discordActivityName' => ['bail', 'integer', 'in:' . implode(',', DiscordActivityName::getValues())],
            'ratingStyle' => ['bail', 'integer', 'in:' . implode(',', RatingStyle::getValues())],
        ];
    }
}
