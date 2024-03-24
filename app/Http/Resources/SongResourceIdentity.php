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
     * @var Song|int $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => (int) ($this->resource?->id ?? $this->resource),
            'uuid'  => (string) ($this->resource?->id ?? $this->resource),
            'type'  => 'songs',
            'href'  => route('api.songs.details', $this->resource, false),
        ];
    }
}
