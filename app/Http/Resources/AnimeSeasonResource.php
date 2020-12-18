<?php

namespace App\Http\Resources;

use App\Models\AnimeSeason;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeSeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @param AnimeSeason $animeSeason */
        $animeSeason = $this->resource;

        return [
            'id'            => $animeSeason->id,
            'type'          => 'seasons',
            'href'          => route('api.seasons.details', $animeSeason, false),
            'attributes'    => [
                'number'        => $animeSeason->number,
                'title'         => $animeSeason->getTitle(),
                'episodeCount'  => $animeSeason->getEpisodeCount(),
                'firstAired'    => $animeSeason->getFirstAired(),
            ]
        ];
    }
}
