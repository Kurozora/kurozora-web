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
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = AnimeResourceIdentity::make($this->resource)->toArray($request);
        $studio = $this->resource->studios;
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
                'poster'                => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Poster)),
                'banner'                => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Banner)),
                'logo'                  => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Logo)),
                'originalTitle'         => $this->resource->original_title,
                'title'                 => $this->resource->title,
                'synonymTitles'         => $this->resource->synonym_titles,
                'tagline'               => $this->resource->tagline,
                'synopsis'              => $this->resource->synopsis,
                'genres'                => $this->resource->genres->pluck('name'),
                'themes'                => $this->resource->themes->pluck('name'),
                'studio'                => $studio?->name,
                'languages'             => LanguageResource::collection($this->resource->languages),
                'countryOfOrigin'       => CountryResource::make($this->resource->country_of_origin),
                'tvRating'              => $this->resource->tv_rating->only(['name', 'description']),
                'type'                  => $this->resource->media_type->only(['name', 'description']),
                'source'                => $this->resource->source->only(['name', 'description']),
                'status'                => $this->resource->status->only(['name', 'description', 'color']),
                'episodeCount'          => $this->resource->episode_count,
                'seasonCount'           => $this->resource->season_count,
                'stats'                 => MediaStatsResource::make($this->resource->mediaStat),
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
        $givenRating = $this->resource->mediaRatings->first();
        $library = $this->resource->relationLoaded('library')
            ? $this->resource->library->first()
            : $this->resource->pivot;

        // Return the array
        return [
            'library' => [
                'rating' => (double) $givenRating?->rating,
                'review' => $givenRating?->description,
                'status' => $library?->status,
                'rewatchCount' => $library?->rewatch_count,
                'isHidden' => (bool) $library?->isHidden,
                'isFavorited' => (bool) $this->resource->isFavorited,
                'isReminded' => $this->resource->isReminded,
            ]
        ];
    }
}
