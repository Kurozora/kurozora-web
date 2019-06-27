<?php

namespace App\Http\Resources;

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
        return [
            'id'            => $this->id,
            'anime_id'      => $this->anime_id,
            'title'         => $this->getTitle(),
            'number'        => $this->number,
            'episode_count' => $this->getEpisodeCount()
        ];
    }
}
