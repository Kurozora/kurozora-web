<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaceDetectionsBatchRequest extends FormRequest
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
            'data' => ['bail', 'required', 'array', 'min:1', 'max:100'],
            'data.*.media_id' => ['bail', 'required', 'integer'],
            'data.*.focal_x' => ['bail', 'nullable', 'numeric', 'between:0,1'],
            'data.*.focal_y' => ['bail', 'nullable', 'numeric', 'between:0,1'],
            'data.*.detector_id' => ['bail', 'required', 'string', 'max:64'],
        ];
    }
}
