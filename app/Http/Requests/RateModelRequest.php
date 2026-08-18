<?php

namespace App\Http\Requests;

use App\Enums\ReviewRecommendation;
use App\Models\MediaRating;
use App\Models\RatingCategoryScore;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class RateModelRequest extends FormRequest
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
            'rating' => ['bail', 'required_without:categoryScores', 'numeric', 'between:' . MediaRating::MIN_RATING_VALUE . ',' . MediaRating::MAX_RATING_VALUE],
            'description' => ['bail', 'string'],
            'note' => ['bail', 'nullable', 'string'],
            'isSpoiler' => ['bail', 'sometimes', 'boolean'],
            // Required once the request carries review content; a score-only quick rating states no opinion.
            'recommendation' => ['bail', 'required_with:description,categoryScores,isSpoiler', 'integer', new EnumValue(ReviewRecommendation::class, false)],
            'categoryScores' => ['bail', 'array', 'min:1'],
            'categoryScores.*' => ['bail', 'numeric', 'between:' . RatingCategoryScore::MIN_SCORE_VALUE . ',' . RatingCategoryScore::MAX_SCORE_VALUE],
            'categoryReviews' => ['bail', 'array'],
            'categoryReviews.*' => ['bail', 'nullable', 'string']
        ];
    }
}
