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
        return [
            'id'            => $this->resource->id,
            'type'          => 'studios',
            'href'          => route('api.studios.details', $this->resource, false),
            'attributes'    => [
                'name'          => $this->resource->name,
                'logo'          => StudioImageResource::make($this->resource->logo_url),
                'about'         => $this->resource->about,
                'founded'       => $this->resource->founded?->format('Y-m-d'),
                'websiteUrl'    => $this->resource->website_url,
                'is_producer'   => $this->whenPivotLoaded(AnimeStudio::TABLE_NAME, function () {
                    return $this->pivot->is_producer;
                }),
                'is_studio'     => $this->whenPivotLoaded(AnimeStudio::TABLE_NAME, function () {
                    return $this->pivot->is_studio;
                }),
                'is_licensor'   => $this->whenPivotLoaded(AnimeStudio::TABLE_NAME, function () {
                    return $this->pivot->is_licensor;
                }),
            ]
        ];
    }
}
