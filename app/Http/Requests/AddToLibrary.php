<?php

namespace App\Http\Requests;

use App\Rules\ValidateAnimeID;
use App\Rules\ValidateLibraryStatus;
use App\User;

class AddToLibrary extends KuroFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user can add to this library
        $user = User::find($this->route('user'))->first();

        return $user && $this->user()->can('add_to_library', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'anime_id'  => new ValidateAnimeID,
            'status'    => new ValidateLibraryStatus
        ];
    }
}
