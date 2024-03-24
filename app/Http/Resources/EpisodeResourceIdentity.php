<?php

namespace App\Http\Resources;

use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Episode|int $resource
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
            'type'  => 'episodes',
            'href'  => route('api.episodes.details', $this->resource, false),
        ];
    }
}
