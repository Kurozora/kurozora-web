<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAnimeReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user can get this user's favorites
        $user = $this->route('user');

        return $this->user()->can('get_anime_reminders', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
