<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchAnimeRequest extends FormRequest
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
            'query' => ['bail', 'required', 'string', 'min:1'],
            'limit' => ['integer', 'min:1', 'max:10']
        ];
    }
}
