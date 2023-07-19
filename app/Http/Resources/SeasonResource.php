<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
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
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = SeasonResourceIdentity::make($this->resource)->toArray($request);
        return array_merge($resource, [
            'attributes'    => [
                'poster'        => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Poster)),
                'number'        => $this->resource->number,
                'title'         => $this->resource->title,
                'synopsis'      => $this->resource->synopsis,
                'episodeCount'  => $this->resource->episodes()->count(),
                'startedAt'     => $this->resource->started_at?->timestamp,
                'firstAired'    => $this->resource->started_at?->timestamp,
                'endedAt'       => $this->resource->ended_at?->timestamp,
            ]
        ]);
    }
}
