<?php

namespace App\Http\Requests;

use App\Enums\StoreProductType;
use Illuminate\Foundation\Http\FormRequest;

class GetStoreRequest extends FormRequest
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
            'type' => ['bail', 'nullable', 'integer', 'in:'. implode(',', StoreProductType::getValues())]
        ];
    }
}
