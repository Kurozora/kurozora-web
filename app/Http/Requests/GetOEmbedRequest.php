<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class GetOEmbedRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new ModelNotFoundException($validator->getMessageBag()->first());
    }

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'format' => ['nullable', 'string', 'in:xml,json']
        ];
    }
}
