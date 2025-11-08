<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use App\Rules\ValidateLibraryStatus;
use Illuminate\Foundation\Http\FormRequest;

class AddToLibraryRequest extends FormRequest
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
            'status' => ['bail', 'required', new ValidateLibraryStatus],
            'library' => ['bail', 'required', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'model_id' => ['bail', 'string'], // TODO: - Remove in favor of model_ids
            'model_ids' => ['bail', 'array', 'max:25'],
            'model_ids*' => ['bail', 'string'],
        ];
    }
}
