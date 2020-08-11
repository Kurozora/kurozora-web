<?php

namespace App\Http\Resources;

use App\Anime;
use App\Enums\AnimeSource;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\DayOfWeek;
use App\Enums\UserLibraryStatus;
use App\Enums\WatchRating;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResourceBasic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        $firstAired = $anime->first_aired;
        if($firstAired)
            $firstAired = $anime->first_aired->format('Y-m-d');

        $lastAired = $anime->last_aired;
        if($lastAired)
            $lastAired = $anime->last_aired->format('Y-m-d');

        $resource = [
            'id'            => $anime->id,
            'type'          => 'show',
            'href'          => route('api.anime.view', $anime, false),
            'attributes'    => [
                'anidbID'               => $anime->anidb_id,
                'anilistID'             => $anime->anilist_id,
                'kitsuID'               => $anime->kitsu_id,
                'imdbID'                => $anime->imdb_id,
                'malID'                 => $anime->mal_id,
                'title'                 => $anime->title,
                'tagline'               => $anime->tagline,
                'synopsis'              => $anime->synopsis,
                'type'                  => AnimeType::getDescription($anime->type),
                'watchRating'           => WatchRating::getDescription($anime->watch_rating),
                'adaptationSource'      => AnimeSource::getDescription($anime->adaptation_source),
                'network'               => $anime->network,
                'producer'              => $anime->producer,
                'episodeCount'          => $anime->episode_count,
                'seasonCount'           => $anime->season_count,
                'averageRating'         => $anime->average_rating,
                'ratingCount'           => $anime->rating_count,
                'videoUrl'              => $anime->video_url,
                'poster'                => AnimeImageResource::make($anime->poster()),
                'background'            => AnimeImageResource::make($anime->banner()),
                'firstAired'            => $firstAired,
                'lastAired'             => $lastAired,
                'runtime'               => $anime->runtime,
                'airStatus'             => AnimeStatus::getDescription($anime->air_status),
                'airTime'               => $anime->air_time,
                'airDay'                => DayOfWeek::getDescription($anime->air_day),
                'isNSFW'                => (bool) $anime->is_nsfw,
                'copyright'             => $anime->copyright
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
    protected function getUserSpecificDetails(): array
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        /** @var User $user */
        $user = Auth::user();

        // Get the user rating for this Anime
        $userRating = null;

        $foundRating = $anime->ratings()
            ->where('user_id', $user->id)
            ->first();

        if($foundRating)
            $userRating = $foundRating->rating;

        // Get the current library status
        $libraryEntry = $user->library()->where('anime_id', $anime->id)->first();
        $currentLibraryStatus = null;

        if($libraryEntry)
            $currentLibraryStatus = UserLibraryStatus::getDescription($libraryEntry->pivot->status);

        // Return the array
        return [
            'currentUser' => [
                'givenRating'       => (double) $userRating,
                'libraryStatus'     => $currentLibraryStatus,
                'isFavorite'        => $user->favoriteAnime()->wherePivot('anime_id', $anime->id)->exists(),
                'isReminded'        => $user->reminderAnime()->wherePivot('anime_id', $this->id)->exists()
            ]
        ];
    }
}
