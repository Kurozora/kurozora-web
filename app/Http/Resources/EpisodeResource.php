<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Enums\WatchStatus;
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
                'previewImage'  => $this->resource->preview_image,
                'number'        => $this->resource->number,
                'title'         => $this->resource->title,
                'synopsis'      => $this->resource->synopsis,
                'duration'      => $this->resource->duration,
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

        /** @var Season $season */
        $season = Season::where('id', $this->resource->season_id)->first();

        /** @var Anime $anime */
        $anime = $season->anime()->first();

        $watchStatus = WatchStatus::Disabled();
        if ($user->isTracking($anime)) {
            $watchStatus = WatchStatus::fromBool($user->watchedEpisodes()->where('episode_id', $this->resource->id)->exists());
        }

        // Return the array
        return [
            'isWatched' => $watchStatus->boolValue
        ];
    }
}
