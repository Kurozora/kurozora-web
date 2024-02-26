<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLibraryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'library' => ['bail', 'required_without:anime_id', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'model_id' => ['bail', 'required_without:anime_id', 'string'],
            'is_hidden' => ['bail', 'nullable', 'boolean'],
            'rewatch_count' => ['bail', 'nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
