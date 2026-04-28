<?php

namespace App\Http\Requests;

use App\Http\Sorters\UserDateSorter;
use App\Http\Sorters\UserFollowersSorter;
use App\Http\Sorters\UserNameSorter;
use App\Http\Sorters\UserReputationSorter;
use App\Rules\ValidateIntegerOrPublicID;
use Illuminate\Foundation\Http\FormRequest;
use kiritokatklian\SortRequest\Traits\SortsViaRequest;

class GetUserIndexRequest extends FormRequest
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
     * Prepare the data for validation.
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
        return array_merge([
            'ids' => ['bail', 'nullable', 'array', 'max:25'],
            'ids.*' => ['bail', new ValidateIntegerOrPublicID()],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
            'page' => ['bail', 'integer', 'min:1'],
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
            'reputation' => UserReputationSorter::class,
            'followers' => UserFollowersSorter::class,
            'date' => UserDateSorter::class,
            'name' => UserNameSorter::class,
        ];
    }
}

