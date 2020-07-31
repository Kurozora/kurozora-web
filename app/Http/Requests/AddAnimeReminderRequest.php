<?php

namespace App\Http\Requests;

use App\Rules\ValidateAnimeID;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

class AddAnimeReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Check if the user can add to this user's favorites
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()->can('add_to_anime_reminder', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'anime_id'      => ['bail', 'required', new ValidateAnimeID],
            'is_reminded'   => ['bail', 'required', 'boolean']
        ];
    }
}
