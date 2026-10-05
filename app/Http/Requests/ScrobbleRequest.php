<?php

namespace App\Http\Requests;

use App\Enums\WatchedKind;
use Illuminate\Foundation\Http\FormRequest;

class ScrobbleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $isStop = $this->routeIs('*.scrobble.stop');

        return [
            'kind' => ['bail', 'nullable', 'integer', 'in:' . implode(',', WatchedKind::getValues())],
            'progress' => ['bail', $isStop ? 'required' : 'nullable', 'numeric', 'min:0', 'max:100'],
            'position' => ['bail', 'nullable', 'integer', 'min:0'],
            'watchedAt' => ['bail', 'nullable', 'integer', 'min:0'],
            'source' => ['bail', 'nullable', 'string', 'max:255'],
            'url' => ['bail', 'nullable', 'string', 'url:http,https', 'max:512'],
            'episode' => ['bail', 'required_without:anime', 'array'],
            'episode.kurozoraID' => ['bail', 'nullable', 'string'],
            'anime' => ['bail', 'required_without:episode', 'array'],
            'anime.ids' => ['bail', 'required_with:anime', 'array'],
            'anime.ids.mal' => ['bail', 'nullable', 'integer'],
            'anime.ids.anilist' => ['bail', 'nullable', 'integer'],
            'anime.ids.kitsu' => ['bail', 'nullable', 'integer'],
            'anime.ids.anidb' => ['bail', 'nullable', 'integer'],
            'anime.ids.tvdb' => ['bail', 'nullable', 'integer'],
            'anime.ids.imdb' => ['bail', 'nullable', 'string'],
            'anime.season' => ['bail', 'nullable', 'integer', 'min:0'],
            'anime.number' => ['bail', 'required_with:anime', 'integer', 'min:0'],
            'anime.isAbsolute' => ['bail', 'nullable', 'boolean'],
        ];
    }
}
