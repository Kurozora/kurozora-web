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
        // Check if the user can add to this library
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()->can('add_to_library', $user);
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
