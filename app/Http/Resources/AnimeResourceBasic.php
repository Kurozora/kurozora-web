<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Enums\DayOfWeek;
use App\Enums\UserLibraryStatus;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Anime $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = [
            'id'            => $this->resource->id,
            'type'          => 'show',
            'href'          => route('api.anime.view', $this->resource, false),
            'attributes'    => [
                'anidbID'               => $this->resource->anidb_id,
                'anilistID'             => $this->resource->anilist_id,
                'imdbID'                => $this->resource->imdb_id,
                'kitsuID'               => $this->resource->kitsu_id,
                'malID'                 => $this->resource->mal_id,
                'notifyID'              => $this->resource->notify_id,
                'syoboiID'              => $this->resource->syoboi_id,
                'traktID'               => $this->resource->trakt_id,
                'tvdbID'                => $this->resource->tvdb_id,
                'videoUrl'              => $this->resource->video_url,
                'poster'                => AnimeImageResource::make($this->resource->poster()),
                'banner'                => AnimeImageResource::make($this->resource->banner()),
                'originalTitle'         => $this->resource->original_title,
                'title'                 => $this->resource->title,
                'tagline'               => $this->resource->tagline,
                'synopsis'              => $this->resource->synopsis,
                'genres'                => $this->resource->genres->pluck('name'),
                'languages'             => LanguageResource::collection($this->resource->languages),
                'tvRating'              => $this->resource->tv_rating->only(['name', 'description']),
                'type'                  => $this->resource->media_type->only(['name', 'description']),
                'source'                => $this->resource->source->only(['name', 'description']),
                'status'                => $this->resource->status->only(['name', 'description', 'color']),
                'episodeCount'          => $this->resource->episode_count,
                'seasonCount'           => $this->resource->season_count,
                'userRating'            => [
                    'ratingCountList'   => [
//                        80, // 1 star
//                        68, // 2 stars
//                        187, // 3 stars
//                        530, // 4 stars
//                        4110 // 5 stars
                    ],
                    'averageRating'     => $this->resource->average_rating,
                    'ratingCount'       => $this->resource->rating_count,
                ],
                'firstAired'            => $this->resource->first_aired?->format('Y-m-d'),
                'lastAired'             => $this->resource->last_aired?->format('Y-m-d'),
                'runtime'               => $this->resource->runtime_string,
                'runtimeTotal'          => $this->resource->runtime_total,
                'airSeason'             => $this->resource->air_season_string,
                'airTime'               => $this->resource->air_time_utc,
                'airDay'                => DayOfWeek::getDescription($this->resource->air_day) ?: null,
                'isNSFW'                => (bool) $this->resource->is_nsfw,
                'copyright'             => $this->resource->copyright,
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

        // Get the user rating for this Anime
        $givenRating = $this->resource->ratings()
            ->firstWhere('user_id', $user->id);

        // Get the current library status
        $libraryEntry = $user->library()->firstWhere('anime_id', $this->resource->id);
        $currentLibraryStatus = null;
        if ($libraryEntry) {
            $currentLibraryStatus = UserLibraryStatus::getDescription($libraryEntry->pivot->status);
        }

        // Get the favorite status
        $isTrackingAnime = $user->isTracking($this->resource);
        $favoriteStatus = null;
        if ($isTrackingAnime) {
            $favoriteStatus = $user->favoriteAnime()->wherePivot('anime_id', $this->resource->id)->exists();
        }

        // Get the reminder status
        $reminderStatus = null;
        if ($isTrackingAnime) {
            $reminderStatus = $user->reminderAnime()->wherePivot('anime_id', $this->resource->id)->exists();
        }

        // Return the array
        return [
            'givenRating'       => (double) $givenRating?->rating,
            'libraryStatus'     => $currentLibraryStatus,
            'isFavorited'       => $favoriteStatus,
            'isReminded'        => $reminderStatus
        ];
    }
}
