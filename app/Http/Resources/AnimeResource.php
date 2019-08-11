<?php

namespace App\Http\Resources;

use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'type'                  => AnimeType::getDescription($this->type),
            'imdb_id'               => $this->imdb_id,
            'network'               => $this->network,
            'status'                => AnimeStatus::getDescription($this->status),
            'episodes'              => $this->episode_count,
            'seasons'               => $this->season_count,
            'average_rating'        => $this->average_rating,
            'rating_count'          => $this->rating_count,
            'synopsis'              => $this->synopsis,
            'runtime'               => $this->runtime,
            'watch_rating'          => $this->watch_rating,
            'tagline'               => $this->tagline,
            'video_url'             => $this->video_url,
            'poster'                => $this->getPoster(false),
            'poster_thumbnail'      => $this->getPoster(true),
            'background'            => $this->getBackground(false),
            'background_thumbnail'  => $this->getBackground(true),
            'nsfw'                  => (bool) $this->nsfw,
            'genres'                => GenreResource::collection($this->genres)
        ];
    }
}
