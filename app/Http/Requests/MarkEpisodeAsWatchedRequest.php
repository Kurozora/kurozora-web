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
    public function authorize(): bool
    {
        /** @var AnimeEpisode $episode */
        $episode = $this->route('episode');

        return $this->user()->can('mark_as_watched', $episode);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
