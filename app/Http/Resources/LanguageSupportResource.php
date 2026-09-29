<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageSupportResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Anime|Game|Manga $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $primaryLanguage = $this->resource->primaryLanguage();

        return [
            'name' => $primaryLanguage?->name,
            'code' => $primaryLanguage?->code,
            'iso6393' => $primaryLanguage?->iso_639_3,
            'types' => $this->resource->primaryLanguageTypes(),
            'count' => (int) $this->resource->supported_languages_count,
        ];
    }
}
