<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryStatus;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MangaResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Manga $resource
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
        $resource = MangaResourceIdentity::make($this->resource)->toArray($request);
        $studio = $this->resource->studios();
        $studio = $studio->firstWhere('is_publisher', '=', true) ?? $studio->first();
        $resource = array_merge($resource, [
            'attributes'    => [
                'anidbID'               => $this->resource->anidb_id,
                'anilistID'             => $this->resource->anilist_id,
                'animePlanetID'         => $this->resource->animeplanet_id,
                'anisearchID'           => $this->resource->anisearch_id,
                'kitsuID'               => $this->resource->kitsu_id,
                'malID'                 => $this->resource->mal_id,
                'slug'                  => $this->resource->slug,
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
                'volumeCount'           => $this->resource->volume_count,
                'chapterCount'          => $this->resource->chapter_count,
                'pageCount'             => $this->resource->page_count,
                'stats'                 => MediaStatsResource::make($this->resource->getMediaStat()),
                'startedAt'             => $this->resource->started_at?->timestamp,
                'endedAt'               => $this->resource->ended_at?->timestamp,
                'duration'              => $this->resource->duration_string,
                'durationTotal'         => $this->resource->duration_total,
                'publicationSeason'     => $this->resource->publication_season?->description,
                'publicationTime'       => $this->resource->publication_time_utc,
                'publicationDay'        => $this->resource->publication_day?->description,
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

        // Get the user rating for this Manga
        $givenRating = $this->resource->ratings()
            ->firstWhere('user_id', $user->id);

        // Get the current library status
        $libraryEntry = $user->whereTracked(Manga::class)
            ->firstWhere([
                ['trackable_id', $this->resource->id],
                ['trackable_type', Manga::class]
            ]);
        $currentLibraryStatus = null;

        if ($libraryEntry) {
            $currentLibraryStatus = UserLibraryStatus::getDescription($libraryEntry->pivot->status);
        }

        // Get the favorite status
        $hasTracked = $user->hasTracked($this->resource);
        $favoriteStatus = null;
        if ($hasTracked) {
            $favoriteStatus = $user->hasFavorited($this->resource);
        }

        // Return the array
        return [
            'givenRating'       => (double) $givenRating?->rating,
            'libraryStatus'     => $currentLibraryStatus,
            'isFavorited'       => $favoriteStatus,
        ];
    }
}