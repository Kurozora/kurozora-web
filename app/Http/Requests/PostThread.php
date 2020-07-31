<?php

namespace App\Http\Requests;

use App\Rules\ValidateThreadContent;
use App\Rules\ValidateThreadTitle;
use Illuminate\Foundation\Http\FormRequest;

class PostThread extends FormRequest
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
            'title'     => ['bail', 'required', new ValidateThreadTitle],
            'content'   => ['bail', 'required', new ValidateThreadContent],
        ];
    }
}
