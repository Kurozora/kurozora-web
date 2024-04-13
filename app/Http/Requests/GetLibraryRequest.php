<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use App\Http\Sorters\LibraryDateSorter;
use App\Http\Sorters\LibraryMyRatingSorter;
use App\Http\Sorters\LibraryPopularitySorter;
use App\Http\Sorters\LibraryRatingSorter;
use App\Http\Sorters\LibraryTitleSorter;
use App\Rules\ValidateLibraryStatus;
use Illuminate\Foundation\Http\FormRequest;
use kiritokatklian\SortRequest\Traits\SortsViaRequest;

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
            'library'   => ['bail', 'nullable', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
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
            'title' => LibraryTitleSorter::class,
            'age' => LibraryDateSorter::class,
            'date' => LibraryDateSorter::class,
            'popularity' => LibraryPopularitySorter::class,
            'rating' => LibraryRatingSorter::class,
            'my-rating' => LibraryMyRatingSorter::class
        ];
    }
}
