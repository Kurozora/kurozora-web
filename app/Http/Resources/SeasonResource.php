<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Enums\WatchStatus;
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
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = SeasonResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes' => [
                'poster' => ImageResource::make(
                    $this->resource->media->firstWhere('collection_name', '=', MediaCollection::Poster) ??
                    $this->resource->anime->media->firstWhere('collection_name', '=', MediaCollection::Poster)
                ),
                'number' => $this->resource->number,
                'title' => $this->resource->title,
                'synopsis' => $this->resource->synopsis,
                'episodeCount' => $this->resource->episodes_count,
                'ratingAverage' => round($this->resource->rating_average ?? 0, 1),
                'startedAt' => $this->resource->started_at?->timestamp,
                'firstAired' => $this->resource->started_at?->timestamp,
                'endedAt' => $this->resource->ended_at?->timestamp,
            ]
        ]);

        if (auth()->check()) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());
        }

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        $user = auth()->user();
        $anime = $this->resource->anime;

        // Get watch status
        $watchStatus = WatchStatus::Disabled();
        if ($user->hasTracked($anime)) {
            $watchStatus = WatchStatus::fromBool($user->hasWatchedSeason($this->resource));
        }

        // Return the array
        return [
            'isWatched' => $watchStatus->boolValue
        ];
    }
}
