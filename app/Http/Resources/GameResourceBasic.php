<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game $resource
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
        $resource = GameResourceIdentity::make($this->resource)->toArray($request);
        $studio = $this->resource->studios;
        $studio = $studio->firstWhere('is_studio', '=', true) ?? $studio->first();
        $resource = array_merge($resource, [
            'attributes'    => [
                'igdbID'                => $this->resource->igdb_id,
                'igdbSlug'              => $this->resource->igdb_slug,
                'slug'                  => $this->resource->slug,
                'videoUrl'              => $this->resource->video_url,
                'poster'                => MediaResource::make($this->resource->getFirstMedia(MediaCollection::Poster)),
                'banner'                => MediaResource::make($this->resource->getFirstMedia(MediaCollection::Banner)),
                'logo'                  => MediaResource::make($this->resource->getFirstMedia(MediaCollection::Logo)),
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
                'editionCount'          => $this->resource->edition_count,
                'stats'                 => MediaStatsResource::make($this->resource->mediaStat),
                'publishedAt'           => $this->resource->published_at?->timestamp,
                'duration'              => $this->resource->duration_string,
                'durationCount'         => $this->resource->duration,
                'durationTotal'         => $this->resource->duration_total_string,
                'durationTotalCount'    => $this->resource->duration_total,
                'publicationSeason'     => $this->resource->publication_season?->description,
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
