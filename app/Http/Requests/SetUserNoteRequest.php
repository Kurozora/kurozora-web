<?php

namespace App\Http\Requests;

use App\Enums\ReviewKind;
use Illuminate\Foundation\Http\FormRequest;

class SetUserNoteRequest extends FormRequest
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
            'kind' => ['bail', 'required', 'integer', 'in:' . implode(',', ReviewKind::getValues())],
            'model_id' => ['bail', 'required', 'string'],
            // An empty body clears the note.
            'body' => ['bail', 'present', 'nullable', 'string', 'max:5000'],
        ];
    }
}
