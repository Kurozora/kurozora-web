<?php

namespace App\Http\Resources;

use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\UserLibraryStatus;
use App\Enums\WatchRating;
use App\User;
use App\UserLibrary;
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
        $resource = [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'type'                  => AnimeType::getDescription($this->type),
            'anidb_id'              => $this->anidb_id,
            'anilist_id'            => $this->anilist_id,
            'kitsu_id'              => $this->kitsu_id,
            'imdb_id'               => $this->imdb_id,
            'mal_id'                => $this->mal_id,
            'network'               => $this->network,
            'status'                => AnimeStatus::getDescription($this->status),
            'episodes'              => $this->episode_count,
            'seasons'               => $this->season_count,
            'average_rating'        => $this->average_rating,
            'rating_count'          => $this->rating_count,
            'synopsis'              => $this->synopsis,
            'runtime'               => $this->runtime,
            'watch_rating'          => WatchRating::getDescription($this->watch_rating),
            'tagline'               => $this->tagline,
            'video_url'             => $this->video_url,
            'poster'                => $this->getPoster(false),
            'poster_thumbnail'      => $this->getPoster(true),
            'background'            => $this->getBackground(false),
            'background_thumbnail'  => $this->getBackground(true),
            'nsfw'                  => (bool) $this->nsfw,
            'genres'                => GenreResource::collection($this->genres),
            'first_aired'           => $this->first_aired,
            'last_aired'            => $this->last_aired,
	        'air_time'              => $this->air_time,
	        'air_day'               => $this->air_day
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
        /** @var User $user */
        $user = Auth::user();

        // Get the user rating for this Anime
        $userRating = null;

        $foundRating = $this->ratings()
            ->where('user_id', $user->id)
            ->first();

        if($foundRating)
            $userRating = $foundRating->rating;

        // Get the current library status
        $libraryEntry = $user->library()->where('anime_id', $this->id)->first();
        $currentLibraryStatus = null;

        if($libraryEntry)
            $currentLibraryStatus = UserLibraryStatus::getDescription($libraryEntry->pivot->status);

        // Return the array
        return [
            'current_user' => [
                'given_rating'      => (double) $userRating,
                'library_status'    => $currentLibraryStatus,
                'is_favorite'       => $user->favoriteAnime()->wherePivot('anime_id', $this->id)->exists()
            ]
        ];
    }
}
