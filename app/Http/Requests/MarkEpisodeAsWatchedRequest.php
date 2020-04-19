<?php

namespace App\Http\Requests;

use App\Rules\ValidateAnimeInLibrary;
use Illuminate\Foundation\Http\FormRequest;

class MarkEpisodeAsWatchedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'watched' => ['bail', 'required', 'numeric', 'in:-1,1']
        ];
    }
}
