<?php

namespace App\Http\Resources;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Genre $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'type'          => 'genres',
            'href'          => route('api.genres.details', $this->resource, false),
            'attributes'    => [
                'name'          => $this->resource->name,
                'color'         => $this->resource->color,
                'symbol'        => $this->resource->symbol,
                'description'   => $this->resource->description,
                'isNSFW'        => (bool) $this->resource->is_nsfw
            ]
        ];
    }
}
