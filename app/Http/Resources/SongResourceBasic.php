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
                'amazonID'  => $this->resource->amazon_id,
                'amID'      => $this->resource->am_id,
                'deezerID'  => $this->resource->deezer_id,
                'malID'     => $this->resource->mal_id,
                'spotifyID' => $this->resource->spotify_id,
                'tidalID'   => $this->resource->tidal_id,
                'youtubeID' => $this->resource->youtube_id,
                'title'     => $this->resource->title,
                'artist'    => $this->resource->artist ?? 'Unknown',
            ]
        ]);
    }
}
