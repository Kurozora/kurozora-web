<?php

namespace App\Http\Requests\Minigames\Kotodama;

use Illuminate\Foundation\Http\FormRequest;

class GetArchivePuzzleRequest extends FormRequest
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
            'date' => ['bail', 'required', 'date_format:Y-m-d', 'before:today'],
        ];
    }
}
