<?php

namespace App\Http\Resources;

use App\Anime;
use App\AnimeEpisode;
use App\AnimeSeason;
use App\Enums\WatchStatus;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AnimeEpisodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AnimeEpisode $animeEpisode */
        $animeEpisode = $this->resource;

        $firstAired = $animeEpisode->first_aired;
        if($firstAired)
            $firstAired = $firstAired->format('j M, Y');

        $resource = [
            'id'            => $animeEpisode->id,
            'type'          => 'episodes',
            'href'          => route('api.episodes.details', $animeEpisode, false),
            'attributes'    => [
                'number'        => $animeEpisode->number,
                'title'         => $animeEpisode->title,
                'overview'      => $animeEpisode->overview,
                'previewImage'  => $animeEpisode->preview_image,
                'firstAired'    => $firstAired,
                'duration'      => $animeEpisode->duration,
                'isVerified'    => (bool) $animeEpisode->verified
            ]
        ];

        if(Auth::check())
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        /** @var AnimeEpisode $animeEpisode */
        $animeEpisode = $this->resource;

        /** @var User $user */
        $user = Auth::user();

        /** @var AnimeSeason $user */
        $season = AnimeSeason::where('id', $animeEpisode->season_id)->first();

        /** @var Anime $anime */
        $anime = $season->anime()->first();

        $watchStatus = WatchStatus::Disabled();
        if($user->isTracking($anime))
            $watchStatus = WatchStatus::fromBool($user->watchedAnimeEpisodes()->where('episode_id', $animeEpisode->id)->exists());

        // Return the array
        return [
            'isWatched' => $watchStatus->boolValue
        ];
    }
}
