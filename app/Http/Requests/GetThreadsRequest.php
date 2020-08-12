<?php

namespace App\Http\Requests;

use App\Enums\ForumOrderType;
use Illuminate\Foundation\Http\FormRequest;

class GetThreadsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order' => ['bail', 'required', 'in:' . implode(',', ForumOrderType::getValues())]
        ];
    }
}
