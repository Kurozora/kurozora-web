<?php

namespace App\Http\Requests;

use App\Rules\ValidateLibraryStatus;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class GetLibrary extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user can get this library
        $user = User::find($this->route('user'))->first();

        return $user && $this->user()->can('get_library', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status'    => ['bail', 'required', new ValidateLibraryStatus],
        ];
    }
}
