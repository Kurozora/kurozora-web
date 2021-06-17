<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Enums\WatchStatus;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Episode $episode */
        $episode = $this->resource;

        $resource = [
            'id'            => $episode->id,
            'type'          => 'episodes',
            'href'          => route('api.episodes.details', $episode, false),
            'attributes'    => [
                'previewImage'  => $episode->preview_image,
                'number'        => $episode->number,
                'title'         => $episode->title,
                'synopsis'      => $episode->synopsis,
                'duration'      => $episode->duration,
                'firstAired'    => $episode->first_aired->format('j M, Y'),
                'isVerified'    => (bool) $episode->verified
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
        /** @var Episode $episode */
        $episode = $this->resource;

        /** @var User $user */
        $user = Auth::user();

        /** @var Season $user */
        $season = Season::where('id', $episode->season_id)->first();

        /** @var Anime $anime */
        $anime = $season->anime()->first();

        $watchStatus = WatchStatus::Disabled();
        if ($user->isTracking($anime)) {
            $watchStatus = WatchStatus::fromBool($user->watchedEpisodes()->where('episode_id', $episode->id)->exists());
        }

        // Return the array
        return [
            'isWatched' => $watchStatus->boolValue
        ];
    }
}
