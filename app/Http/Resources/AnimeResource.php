<?php

namespace App\Http\Resources;

use App\Enums\AnimeSource;
use App\Anime;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\UserLibraryStatus;
use App\Enums\WatchRating;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
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
            'href'          => url()->route('anime.view', $anime, false),
            'attributes'    => [
                'title'                 => $anime->title,
                'type'                  => AnimeType::getDescription($anime->type),
                'source'                => AnimeSource::getDescription($anime->source),
                'anidb_id'              => $anime->anidb_id,
                'anilist_id'            => $anime->anilist_id,
                'kitsu_id'              => $anime->kitsu_id,
                'imdb_id'               => $anime->imdb_id,
                'mal_id'                => $anime->mal_id,
                'network'               => $anime->network,
                'studio'                => StudioResourceSmall::collection($anime->studios),
                'status'                => AnimeStatus::getDescription($anime->status),
                'episodes'              => $anime->episode_count,
                'seasons'               => $anime->season_count,
                'average_rating'        => $anime->average_rating,
                'rating_count'          => $anime->rating_count,
                'synopsis'              => $anime->synopsis,
                'runtime'               => $anime->runtime,
                'watch_rating'          => WatchRating::getDescription($anime->watch_rating),
                'tagline'               => $anime->tagline,
                'video_url'             => $anime->video_url,
                'poster'                => AnimeImageResource::make($anime->poster()),
                'background'            => AnimeImageResource::make($anime->banner()),
                'nsfw'                  => (bool) $anime->nsfw,
                'genres'                => GenreResource::collection($anime->genres),
                'first_aired'           => $firstAired,
                'last_aired'            => $lastAired,
                'air_time'              => $anime->air_time,
                'air_day'               => $anime->air_day,
                'copyright'             => $anime->copyright
            ]
        ];

        if($request->input('include')) {
            $includes = explode(',', $request->input('include'));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'seasons':
                        $relationships = array_merge($relationships, $this->getSeasonsRelationship());
                        break;
                    case 'actors':
                        $relationships = array_merge($relationships, $this->getActorsRelationship());
                        break;
                }
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

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
            'current_user' => [
                'given_rating'      => (double) $userRating,
                'library_status'    => $currentLibraryStatus,
                'is_favorite'       => $user->favoriteAnime()->wherePivot('anime_id', $anime->id)->exists()
            ]
        ];
    }

    /**
     * Returns the seasons relationship for the resource.
     *
     * @return array
     */
    protected function getSeasonsRelationship() {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'seasons' => [
                'data' => AnimeSeasonResource::collection($anime->getSeasons(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => url()->route('anime.seasons', $anime, false)
            ]
        ];
    }

    /**
     * Returns the actors relationship for the resource.
     *
     * @return array
     */
    protected function getActorsRelationship() {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'actors' => [
                'data' => ActorResource::collection($anime->getActors(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => url()->route('anime.actors', $anime, false)
            ]
        ];
    }
}
