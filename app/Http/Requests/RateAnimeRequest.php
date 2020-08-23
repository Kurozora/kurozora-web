<?php

namespace App\Http\Requests;

use App\AnimeRating;
use Illuminate\Foundation\Http\FormRequest;

class RateAnimeRequest extends FormRequest
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
            'rating' => ['bail', 'required', 'numeric', 'between:' . AnimeRating::MIN_RATING_VALUE . ',' . AnimeRating::MAX_RATING_VALUE]
        ];
    }
}
