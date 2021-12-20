<?php

namespace App\Http\Resources;

use App\Enums\WatchStatus;
use App\Models\Episode;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
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
        $resource = [
            'id'            => $this->resource->id,
            'type'          => 'episodes',
            'href'          => route('api.episodes.details', $this->resource, false),
            'attributes'    => [
                'poster'        => ImageResource::make($this->resource->season->poster_image),
                'banner'        => ImageResource::make($this->resource->banner_image),
                'number'        => $this->resource->number,
                'numberTotal'   => $this->resource->number_total,
                'title'         => $this->resource->title,
                'synopsis'      => $this->resource->synopsis,
                'duration'      => $this->resource->duration_string,
                'stats'         => MediaStatsResource::make($this->resource->getStats()),
                'firstAired'    => $this->resource->first_aired?->timestamp,
                'isVerified'    => (bool) $this->resource->verified
            ]
        ];

        if (Auth::check()) {
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
        $user = Auth::user();
        $anime = $this->resource->season->anime;

        // Get the user rating for this episode
        $givenRating = $this->resource->ratings()
            ->firstWhere('user_id', $user->id);

        // Get watch status
        $watchStatus = WatchStatus::Disabled();
        if ($user->isTracking($anime)) {
            $watchStatus = WatchStatus::fromBool($user->episodes()->where('episode_id', $this->resource->id)->exists());
        }

        // Return the array
        return [
            'givenRating'   => (double) $givenRating?->rating,
            'isWatched'     => $watchStatus->boolValue
        ];
    }
}
