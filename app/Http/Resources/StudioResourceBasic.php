<?php

namespace App\Http\Resources;

use App\Models\AnimeStudio;
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
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = StudioResourceIdentity::make($this->resource)->toArray($request);
        return array_merge($resource, [
            'attributes'    => [
                'logo'          => ImageResource::make($this->resource->profile_image),
                'name'          => $this->resource->name,
                'about'         => $this->resource->about,
                'address'       => $this->resource->address,
                'founded'       => $this->resource->founded?->timestamp,
                'websiteUrls'   => $this->resource->website_urls,
                'isProducer'    => $this->whenPivotLoaded(AnimeStudio::TABLE_NAME, function () {
                    return $this->pivot->is_producer;
                }),
                'isStudio'      => $this->whenPivotLoaded(AnimeStudio::TABLE_NAME, function () {
                    return $this->pivot->is_studio;
                }),
                'isLicensor'    => $this->whenPivotLoaded(AnimeStudio::TABLE_NAME, function () {
                    return $this->pivot->is_licensor;
                }),
            ]
        ]);
    }
}
