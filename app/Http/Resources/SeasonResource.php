<?php

namespace App\Http\Resources;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Season $resource
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
            'type'          => 'seasons',
            'href'          => route('api.seasons.details', $this->resource, false),
            'attributes'    => [
                'number'        => $this->resource->number,
                'posterURL'     => $this->resource->poster_url,
                'title'         => $this->resource->title,
                'synopsis'      => $this->resource->synopsis,
                'episodeCount'  => $this->resource->episodes()->count(),
                'firstAired'    => $this->resource->first_aired?->format('Y-m-d'),
            ]
        ];
    }
}