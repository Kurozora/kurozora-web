<?php

namespace App\Http\Requests;

use App\Rules\ValidateAnimeID;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFromLibrary extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user can delete from this library
        $user = User::find($this->route('user'))->first();

        return $user && $this->user()->can('del_from_library', $user);
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
        ];
    }
}
