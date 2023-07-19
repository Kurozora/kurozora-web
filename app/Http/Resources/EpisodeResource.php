<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Enums\WatchStatus;
use App\Models\Episode;
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
    public function toArray(Request $request): array
    {
        $resource = EpisodeResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes'    => [
                'poster'        => ImageResource::make($this->resource->season->getFirstMedia(MediaCollection::Poster)),
                'banner'        => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Banner)),
                'number'        => $this->resource->number,
                'numberTotal'   => $this->resource->number_total,
                'title'         => $this->resource->title,
                'synopsis'      => $this->resource->synopsis,
                'duration'      => $this->resource->duration_string,
                'stats'         => MediaStatsResource::make($this->resource->getMediaStats()),
                'videos'        => VideoResource::collection($this->resource->videos),
                'isFiller'      => $this->resource->is_filler,
                'isNsfw'        => $this->resource->is_nsfw,
                'isPremiere'    => $this->resource->is_premiere,
                'isFinale'      => $this->resource->is_finale,
                'isSpecial'     => $this->resource->is_special,
                'isVerified'    => $this->resource->is_verified,
                'startedAt'     => $this->resource->started_at?->timestamp,
                'firstAired'    => $this->resource->started_at?->timestamp,
                'endedAt'       => $this->resource->ended_at?->timestamp,
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

        // Get the user rating for this episode
        $givenRating = $this->resource->mediaRatings()
            ->firstWhere('user_id', $user->id);

        // Get watch status
        $watchStatus = WatchStatus::Disabled();
        if ($user->hasTracked($anime)) {
            $watchStatus = WatchStatus::fromBool($user->episodes()->where('episode_id', $this->resource->id)->exists());
        }

        // Return the array
        return [
            'givenRating'   => (double) $givenRating?->rating,
            'isWatched'     => $watchStatus->boolValue
        ];
    }
}
