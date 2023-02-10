<?php

namespace App\Http\Resources;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Song $resource
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
            'id'    => $this->resource->id,
            'uuid'  => (string) $this->resource->id,
            'type'  => 'songs',
            'href'  => route('api.songs.details', $this->resource, false),
        ];
    }
}
