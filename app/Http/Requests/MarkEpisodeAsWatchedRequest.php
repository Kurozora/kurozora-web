<?php

namespace App\Http\Requests;

use App\AnimeEpisode;
use Illuminate\Foundation\Http\FormRequest;

class MarkEpisodeAsWatchedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $episode = AnimeEpisode::find($this->route('episode'));

        return $this->user()->can('mark_as_watched', $episode);
    }

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
