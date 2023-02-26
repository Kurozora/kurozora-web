<?php

namespace App\Http\Resources;

use App\Models\MediaSong;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaSongResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaSong $resource
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
        $resource = MediaSongResourceBasic::make($this->resource)->toArray($request);

        return $resource;
    }
}
