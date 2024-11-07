<?php

namespace App\Http\Resources;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResourceIdentity extends JsonResource
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
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->id,
            'type' => 'seasons',
            'href' => route('api.seasons.details', $this->resource, false),
        ];
    }
}
