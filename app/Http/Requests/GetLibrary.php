<?php

namespace App\Http\Requests;

use App\Http\Sorters\AnimeAgeSorter;
use App\Http\Sorters\AnimeMyRatingSorter;
use App\Http\Sorters\AnimeRatingSorter;
use App\Rules\ValidateLibraryStatus;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use musa11971\SortRequest\Traits\SortsViaRequest;

class GetLibrary extends FormRequest
{
    use SortsViaRequest;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user can get this library
        $user = $this->route('user');

        return $this->user()->can('get_library', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge([
            'status'    => ['bail', 'required', new ValidateLibraryStatus],
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
