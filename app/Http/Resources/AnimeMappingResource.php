<?php

namespace App\Http\Resources;

use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeMappingResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Anime $resource
     */
    public $resource;

    /**
     * Transform the anime into its slug and the external service identifiers it maps to.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return array_filter([
            'slug' => $this->resource->slug,
            'myanimelist' => $this->resource->mal_id,
            'anilist' => $this->resource->anilist_id,
            'kitsu' => $this->resource->kitsu_id,
            'anidb' => $this->resource->anidb_id,
            'anime_planet' => $this->resource->animeplanet_id,
            'anisearch' => $this->resource->anisearch_id,
            'livechart' => $this->resource->livechart_id,
            'notify' => $this->resource->notify_id,
            'shoboi' => $this->resource->syoboi_id,
            'trakt' => $this->resource->trakt_id,
            'thetvdb' => $this->resource->tvdb_id,
            'imdb' => $this->resource->imdb_id,
        ], static fn ($value) => $value !== null);
    }
}
