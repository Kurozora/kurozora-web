<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetUpcomingEpisodesRequest extends FormRequest
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
            'model_id' => ['bail', 'nullable', 'string'],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
            'page' => ['bail', 'integer', 'min:1']
        ];
    }
}
