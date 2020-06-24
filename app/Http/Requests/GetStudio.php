<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetStudio extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'anime' => ['bail', 'nullable', 'boolean'],
            'limit' => ['bail', 'nullable', 'integer'],
        ];
    }
}
