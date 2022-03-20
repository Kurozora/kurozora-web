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
     * @var Episode $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'type'          => 'episodes',
            'href'          => route('api.episodes.details', $this->resource, false),
        ];
    }
}
