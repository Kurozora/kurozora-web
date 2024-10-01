<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;

class ClearUserLibraryRequest extends FormRequest
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
            'password' => ['required'],
            'library'   => ['bail', 'required_without:anime_id', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
        ];
    }
}
