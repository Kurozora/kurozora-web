<?php

namespace App\Http\Requests;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'scope'     => ['bail', 'required', 'string', 'in:' . implode(',', SearchScope::getValues())],
            'types'     => ['bail', 'required', 'array'],
            'types.*'   => ['bail', 'required', 'string', 'distinct', 'in:' . implode(',', SearchType::getValues())],
            'query'     => ['bail', 'required', 'string', 'min:1'],
            'limit'     => ['bail', 'integer', 'min:1', 'max:25'],
            'page'      => ['bail', 'integer', 'min:1']
        ];
    }
}