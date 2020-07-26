<?php

namespace App\Http\Resources;

use App\AnimeEpisode;
use App\AnimeSeason;
use App\Enums\WatchStatus;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AnimeEpisodeResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
    public function toArray($request)
    {
        /** @var AnimeEpisode $animeEpisode */
        $animeEpisode = $this->resource;

        $firstAired = $animeEpisode->first_aired;
        if($firstAired)
            $firstAired = $firstAired->format('j M, Y');

        $resource = [
            'id'            => $animeEpisode->id,
            'type'          => 'episodes',
            'href'          => route('seasons.episodes', $animeEpisode, false),
            'attributes'    => [
                'number'        => $animeEpisode->number,
                'name'          => $animeEpisode->name,
                'first_aired'   => $firstAired,
                'overview'      => $animeEpisode->overview,
                'verified'      => (bool) $animeEpisode->verified
            ]
        ];

        if(Auth::check())
            $resource = array_merge($resource, $this->getUserSpecificDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails() {
        /** @var AnimeEpisode $animeEpisode */
        $animeEpisode = $this->resource;

        $user = Auth::user();
        $season = AnimeSeason::where('id', $animeEpisode->season_id)->first();
	    $anime = $season->anime()->first();

        $watchStatus = WatchStatus::Disabled();
        if($user->isTracking($anime))
	        $watchStatus = WatchStatus::init($user->watchedAnimeEpisodes()->where('episode_id', $animeEpisode->id)->exists());

        // Return the array
        return [
            'current_user' => [
                'watched' => $watchStatus->value
            ]
        ];
    }
}
