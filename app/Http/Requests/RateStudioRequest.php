<?php

namespace App\Http\Requests;

use App\Models\MediaRating;
use Illuminate\Foundation\Http\FormRequest;

class RateStudioRequest extends FormRequest
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
            'rating' => ['bail', 'required', 'numeric', 'between:' . MediaRating::MIN_RATING_VALUE . ',' . MediaRating::MAX_RATING_VALUE],
            'description' => ['bail', 'string']
        ];
    }
}
