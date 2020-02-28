<?php

namespace App\Http\Requests;

use App\Rules\ValidateAnimeID;
use App\Rules\ValidateLibraryStatus;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class AddToLibrary extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user can add to this library
        $user = $this->route('user');

        return $this->user()->can('add_to_library', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'anime_id'  => ['bail', 'required', new ValidateAnimeID],
            'status'    => ['bail', 'required', new ValidateLibraryStatus],
        ];
    }
}
