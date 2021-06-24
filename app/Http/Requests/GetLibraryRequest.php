<?php

namespace App\Http\Requests;

use App\Http\Sorters\AnimeAgeSorter;
use App\Http\Sorters\AnimeMyRatingSorter;
use App\Http\Sorters\AnimeRatingSorter;
use App\Rules\ValidateLibraryStatus;
use Illuminate\Foundation\Http\FormRequest;
use musa11971\SortRequest\Traits\SortsViaRequest;

class GetLibraryRequest extends FormRequest
{
    use SortsViaRequest;

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
        return array_merge([
            'status'    => ['bail', 'required', new ValidateLibraryStatus],
            'limit'     => ['bail', 'integer', 'min:1', 'max:100'],
            'page'      => ['bail', 'integer', 'min:1']
        ], $this->sortingRules());
    }

    /**
     * Returns the columns that can be sorted on.
     *
     * @return array
     */
    function getSortableColumns(): array
    {
        return [
            'title',
            'age'       => AnimeAgeSorter::class,
            'rating'    => AnimeRatingSorter::class,
            'my-rating' => AnimeMyRatingSorter::class
        ];
    }
}
