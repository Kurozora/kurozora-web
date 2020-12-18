<?php

namespace App\Http\Resources;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
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
                'isNSFW'        => (bool) $genre->is_nsfw
            ]
        ];
    }
}
