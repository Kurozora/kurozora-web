<?php

namespace App\Http\Requests;

use App\Enums\ReviewKind;
use App\Http\Sorters\MediaRatingDateSorter;
use App\Http\Sorters\MediaRatingRatingSorter;
use App\Http\Sorters\MediaRatingTitleSorter;
use App\Models\MediaRating;
use Illuminate\Foundation\Http\FormRequest;
use kiritokatklian\SortRequest\Traits\SortsViaRequest;

class GetUserReviewsRequest extends FormRequest
{
    use SortsViaRequest;

    /**
     * The maximum number of IDs accepted on the overlay branch.
     */
    const int MAX_IDS = 50;

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
     * Splits comma-joined `ids` into an array prior to validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->ids && is_string($this->ids)) {
            $this->merge(['ids' => explode(',', $this->ids)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $kindRule = 'in:' . implode(',', ReviewKind::getValues());

        return array_merge([
            'kind' => ['bail', $this->filled('ids') ? 'required' : 'nullable', 'integer', $kindRule],
            'ids' => ['bail', 'nullable', 'array', 'max:' . self::MAX_IDS],
            'ids.*' => ['bail', 'string'],
            'has_review' => ['bail', 'nullable', 'integer', 'in:0,1'],
            'rating' => ['bail', 'nullable', 'numeric', 'between:0.5,' . MediaRating::MAX_RATING_VALUE, 'multiple_of:0.5'],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
            'page' => ['bail', 'integer', 'min:1'],
            'offset' => ['bail', 'integer', 'min:0'],
        ], $this->sortingRules());
    }

    /**
     * Get the sortable columns of the request.
     *
     * @return array
     */
    function getSortableColumns(): array
    {
        return [
            'date' => MediaRatingDateSorter::class,
            'title' => MediaRatingTitleSorter::class,
            'rating' => MediaRatingRatingSorter::class,
        ];
    }
}
