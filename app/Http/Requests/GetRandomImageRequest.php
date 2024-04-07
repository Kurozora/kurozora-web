<?php

namespace App\Http\Requests;

use App\Enums\MediaCollection;
use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;

class GetRandomImageRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:' . implode(',', MediaType::getValues())],
            'collection' => ['required', 'string', 'in:' . implode(',', MediaCollection::getValues())],
            'limit' => ['nullable', 'numeric', 'min:1', 'max:25'],
        ];
    }
}
