<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['bail', 'required', 'string', 'in:' . implode(',', UserLibraryKind::getValues())],
            'date' => ['bail', 'nullable', 'string', 'date', 'date_format:Y-m-d'],
        ];
    }
}
