<?php

namespace App\Http\Resources;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResourceBasic extends JsonResource
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
        $resource = SongResourceIdentity::make($this->resource)->toArray($request);

        return array_merge($resource, [
            'attributes'    => [
                'amID'      => $this->resource->am_id,
                'malID'     => $this->resource->mal_id,
                'title'     => $this->resource->title,
                'artist'    => $this->resource->artist,
            ]
        ]);
    }
}
