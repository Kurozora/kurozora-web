<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFromLibraryRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->model_ids && is_string($this->model_ids)) {
            $this->merge(['model_ids' => explode(',', $this->model_ids)]);
        }
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
            'model_id' => ['bail', 'string'],
            'model_ids' => ['bail', 'array', 'max:25'],
            'model_ids*' => ['bail', 'string'],
        ];
    }
}
