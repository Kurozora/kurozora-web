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

        return array_merge($resource, [
            'attributes' => [
                'slug' => $this->resource->slug,
                'profile' => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Profile)),
                'banner' => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Banner)),
                'logo' => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Logo)),
                'name' => $this->resource->name,
                'japaneseName' => $this->resource->japanese_name,
                'alternativeNames' => $this->resource->alternative_names,
                'predecessors' => $this->resource->predecessors?->pluck('name')->toArray(),
                'successors' => $this->resource->successor?->name,
                'about' => $this->resource->about,
                'address' => $this->resource->address,
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
    }
}
