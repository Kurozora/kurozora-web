<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSearchIndexRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:255'],
            'letter' => ['nullable', 'string', 'max:1'],
            'type' => ['nullable', 'string', 'max:64'],
            'perPage' => ['nullable', 'integer', 'in:25,50,100'],
            'filter' => ['nullable', 'array'],
            'order' => ['nullable', 'array'],
            'order.*' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
