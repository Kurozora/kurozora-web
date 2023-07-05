<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Anime;
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
        $resource = AnimeResourceIdentity::make($this->resource)->toArray($request);
        $studio = $this->resource->studios();
        $studio = $studio->firstWhere('is_studio', '=', true) ?? $studio->first();
        $resource = array_merge($resource, [
            'attributes'    => [
                'anidbID'               => $this->resource->anidb_id,
                'anilistID'             => $this->resource->anilist_id,
                'animePlanetID'         => $this->resource->animeplanet_id,
                'anisearchID'           => $this->resource->anisearch_id,
                'imdbID'                => $this->resource->imdb_id,
                'kitsuID'               => $this->resource->kitsu_id,
                'malID'                 => $this->resource->mal_id,
                'notifyID'              => $this->resource->notify_id,
                'syoboiID'              => $this->resource->syoboi_id,
                'traktID'               => $this->resource->trakt_id,
                'tvdbID'                => $this->resource->tvdb_id,
                'slug'                  => $this->resource->slug,
                'videoUrl'              => $this->resource->video_url,
                'poster'                => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Poster)),
                'banner'                => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Banner)),
                'logo'                  => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Logo)),
                'originalTitle'         => $this->resource->original_title,
                'title'                 => $this->resource->title,
                'synonymTitles'         => $this->resource->synonym_titles,
                'tagline'               => $this->resource->tagline,
                'synopsis'              => $this->resource->synopsis,
                'genres'                => $this->resource->genres->pluck('name'),
                'themes'                => $this->resource->themes->pluck('name'),
                'studio'                => $studio?->name,
                'languages'             => LanguageResource::collection($this->resource->languages),
                'tvRating'              => $this->resource->tv_rating->only(['name', 'description']),
                'type'                  => $this->resource->media_type->only(['name', 'description']),
                'source'                => $this->resource->source->only(['name', 'description']),
                'status'                => $this->resource->status->only(['name', 'description', 'color']),
                'episodeCount'          => $this->resource->episode_count,
                'seasonCount'           => $this->resource->season_count,
                'stats'                 => MediaStatsResource::make($this->resource->getMediaStat()),
                'startedAt'             => $this->resource->started_at?->timestamp,
                'firstAired'            => $this->resource->started_at?->timestamp,
                'endedAt'               => $this->resource->ended_at?->timestamp,
                'lastAired'             => $this->resource->ended_at?->timestamp,
                'duration'              => $this->resource->duration_string,
                'durationTotal'         => $this->resource->duration_total,
                'airSeason'             => $this->resource->air_season?->description,
                'airTime'               => $this->resource->air_time_utc,
                'airDay'                => $this->resource->air_day?->description,
                'isNSFW'                => (bool) $this->resource->is_nsfw,
                'copyright'             => $this->resource->copyright,
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

        // Get the user rating for this Anime
        $givenRating = $this->resource->mediaRatings()
            ->firstWhere('user_id', $user->id);

        // Get the current library status
        $libraryEntry = $user->whereTracked(Anime::class)
            ->firstWhere([
                ['trackable_id', $this->resource->id],
                ['trackable_type', $this->resource->getMorphClass()]
            ]);

        // Get various statuses
        $currentLibraryStatus = $libraryEntry ? $libraryEntry->pivot->status : null;
        $favoriteStatus = $libraryEntry ? $this->resource->isFavoritedBy($user) : null;
        $reminderStatus = $libraryEntry ? $user->reminderAnime()->wherePivot('anime_id', $this->resource->id)->exists() : null;

        // Return the array
        return [
            'givenRating'       => (double) $givenRating?->rating,
            'libraryStatus'     => $currentLibraryStatus,
            'isFavorited'       => $favoriteStatus,
            'isReminded'        => $reminderStatus
        ];
    }
}
