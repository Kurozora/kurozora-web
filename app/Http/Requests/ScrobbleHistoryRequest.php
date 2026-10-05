<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScrobbleHistoryRequest extends FormRequest
{
    /**
     * The maximum number of plays accepted per request.
     */
    const int MAX_PLAYS = 100;

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
        return [
            'plays' => ['bail', 'required', 'array', 'max:' . self::MAX_PLAYS],
            'plays.*.watchedAt' => ['bail', 'required', 'integer', 'min:0'],
            'plays.*.source' => ['bail', 'nullable', 'string', 'max:255'],
            'plays.*.episode' => ['bail', 'required_without:plays.*.anime', 'array'],
            'plays.*.episode.kurozoraID' => ['bail', 'nullable', 'string'],
            'plays.*.anime' => ['bail', 'required_without:plays.*.episode', 'array'],
            'plays.*.anime.ids' => ['bail', 'required_with:plays.*.anime', 'array'],
            'plays.*.anime.ids.mal' => ['bail', 'nullable', 'integer'],
            'plays.*.anime.ids.anilist' => ['bail', 'nullable', 'integer'],
            'plays.*.anime.ids.kitsu' => ['bail', 'nullable', 'integer'],
            'plays.*.anime.ids.anidb' => ['bail', 'nullable', 'integer'],
            'plays.*.anime.ids.tvdb' => ['bail', 'nullable', 'integer'],
            'plays.*.anime.ids.imdb' => ['bail', 'nullable', 'string'],
            'plays.*.anime.season' => ['bail', 'nullable', 'integer', 'min:0'],
            'plays.*.anime.number' => ['bail', 'required_with:plays.*.anime', 'integer', 'min:0'],
            'plays.*.anime.isAbsolute' => ['bail', 'nullable', 'boolean'],
        ];
    }
}
