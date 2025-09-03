<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetExplorePageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'genre_id' => ['bail', 'nullable', 'string'],
            'theme_id' => ['bail', 'nullable', 'string'],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
        ];
    }
}
