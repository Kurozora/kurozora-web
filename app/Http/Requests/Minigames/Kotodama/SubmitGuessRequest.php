<?php

namespace App\Http\Requests\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Word;
use Illuminate\Foundation\Http\FormRequest;

class SubmitGuessRequest extends FormRequest
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
            'guess' => ['bail', 'required', 'string', 'regex:/^[a-z]{' . Word::LENGTH . '}$/'],
        ];
    }
}
