<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;

class GetUserReminderRequest extends FormRequest
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
            'library' => ['bail', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
            'page' => ['bail', 'integer', 'min:1']
        ];
    }
}
