<?php

namespace App\Http\Requests\Moderation;

use Illuminate\Foundation\Http\FormRequest;

class AppealTimeoutRequest extends FormRequest
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
            'message' => ['bail', 'required', 'string', 'min:10', 'max:2000'],
        ];
    }
}
