<?php

namespace App\Http\Resources;

use App\Models\AnimeSong;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeSongResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var AnimeSong $resource
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
            'uuid'          => (string) $this->resource->id,
            'type'          => 'anime-songs',
            'href'          => route('api.anime.songs', $this->resource->anime, false),
        ];
    }
}
