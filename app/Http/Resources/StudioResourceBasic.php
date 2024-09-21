<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\MediaStudio;
use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Studio $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = StudioResourceIdentity::make($this->resource)->toArray($request);

        $resource = array_merge($resource, [
            'attributes' => [
                'slug' => $this->resource->slug,
                'profile' => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Profile)),
                'banner' => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Banner)),
                'logo' => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Logo)),
                'name' => $this->resource->name,
                'japaneseName' => $this->resource->japanese_name,
                'alternativeNames' => $this->resource->alternative_names,
                'predecessors' => $this->resource->predecessors?->pluck('name')->toArray(),
                'successor' => $this->resource->successor?->name,
                'about' => $this->resource->about,
                'address' => $this->resource->address,
                'tvRating' => $this->resource->tv_rating->only(['name', 'description']),
                'stats' => MediaStatsResource::make($this->resource->mediaStat),
                'socialURLs' => $this->resource->social_urls,
                'websiteURLs' => $this->resource->website_urls,
                'isProducer' => $this->whenPivotLoaded(MediaStudio::TABLE_NAME, function () {
                    return $this->resource->pivot->is_producer;
                }),
                'isStudio' => $this->whenPivotLoaded(MediaStudio::TABLE_NAME, function () {
                    return $this->resource->pivot->is_studio;
                }),
                'isLicensor' => $this->whenPivotLoaded(MediaStudio::TABLE_NAME, function () {
                    return $this->resource->pivot->is_licensor;
                }),
                'isNSFW' => (bool) $this->resource->is_nsfw,
                'founded' => $this->resource->founded_at?->timestamp, // MARK: - Remove after 1.10.0
                'foundedAt' => $this->resource->founded_at?->timestamp,
                'defunctAt' => $this->resource->defunct_at?->timestamp,
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

        // Return the array
        return [
            'library' => [
                'rating' => (double) $givenRating?->rating,
                'review' => $givenRating?->description,
                'isFavorited' => (bool) $this->resource->isFavorited,
            ]
        ];
    }
}
