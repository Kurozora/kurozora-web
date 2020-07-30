<?php

namespace App\Http\Resources;

use App\Genre;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Genre $genre */
        $genre = $this->resource;

        return [
            'id'            => $genre->id,
            'type'          => 'genres',
            'href'          => route('api.genres.details', $genre, false),
            'attributes'    => [
                'name'          => $genre->name,
                'color'         => $genre->color,
                'symbol'        => $genre->symbol,
                'description'   => $genre->description,
                'nsfw'          => (bool) $genre->nsfw
            ]
        ];
    }
}
