<?php

namespace App\Http\Resources;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Season $season */
        $season = $this->resource;

        return [
            'id'            => $season->id,
            'type'          => 'seasons',
            'href'          => route('api.seasons.details', $season, false),
            'attributes'    => [
                'number'        => $season->number,
                'posterURL'     => $season->poster_url,
                'title'         => $season->title,
                'synopsis'      => $season->synopsis,
                'episodeCount'  => $season->episodes()->count(),
                'firstAired'    => $season->first_aired?->format('Y-m-d'),
            ]
        ];
    }
}
