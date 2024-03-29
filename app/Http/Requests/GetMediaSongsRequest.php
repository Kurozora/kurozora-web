<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetMediaSongsRequest extends FormRequest
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
            'limit'     => ['bail', 'integer', 'min:-1', 'max:100', 'not_in:0'],
            'page'      => ['bail', 'integer', 'min:1']
        ];
    }
}
