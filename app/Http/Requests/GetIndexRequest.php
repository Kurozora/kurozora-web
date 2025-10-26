<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetIndexRequest extends FormRequest
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
        if ($this->ids && is_string($this->ids)) {
            $this->replace(['ids' => explode(',', $this->ids)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['bail', 'nullable', 'array', 'max:25'],
            'ids.*' => ['bail', 'integer'],
        ];
    }
}
