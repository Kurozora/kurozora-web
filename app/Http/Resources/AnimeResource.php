<?php

namespace App\Http\Resources;

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
            'average_rating'        => $this->average_rating,
            'poster'                => $this->getPoster(false),
            'poster_thumbnail'      => $this->getPoster(true),
            'background'            => $this->getBackground(false),
            'background_thumbnail'  => $this->getBackground(true),
            'genres'                => GenreResource::collection($this->genres)
        ];
    }
}
