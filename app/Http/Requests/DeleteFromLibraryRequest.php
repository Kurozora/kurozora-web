<?php

namespace App\Http\Requests;

use App\Anime;
use App\Rules\ValidateAnimeIDIsTracked;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFromLibraryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Check if the user can delete from this library
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()->can('del_from_library', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'anime_id'  => ['bail', 'required', 'integer', 'exists:' . Anime::TABLE_NAME . ',id', new ValidateAnimeIDIsTracked],
        ];
    }
}
