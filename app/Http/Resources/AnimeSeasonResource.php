<?php

namespace App\Http\Resources;

use App\AnimeSeason;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeSeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @param AnimeSeason $animeSeason */
        $animeSeason = $this->resource;

        return [
            'id'            => $animeSeason->id,
            'type'          => 'seasons',
            'href'          => url()->route('seasons.details', $animeSeason, false),
            'attributes'    => [
                'anime_id'      => $animeSeason->anime_id,
                'title'         => $animeSeason->getTitle(),
                'number'        => $animeSeason->number,
                'first_aired'   => $animeSeason->getFirstAired(),
                'episode_count' => $animeSeason->getEpisodeCount()
            ]
        ];
    }
}
