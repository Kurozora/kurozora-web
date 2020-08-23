<?php

namespace App\Http\Requests;

use App\Anime;
use App\Rules\ValidateLibraryStatus;
use App\User;
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'anime_id'  => ['bail', 'required', 'integer'],
            'status'    => ['bail', 'required', new ValidateLibraryStatus],
        ];
    }
}
