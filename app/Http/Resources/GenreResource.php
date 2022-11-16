<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Genre $resource
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
        $resource = GenreResourceIdentity::make($this->resource)->toArray($request);

        return array_merge($resource, [
            'attributes'    => [
                'slug'          => $this->resource->slug,
                'name'          => $this->resource->name,
                'color'         => $this->resource->color,
                'symbol'        => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Symbol)),
                'description'   => $this->resource->description,
                'isNSFW'        => (bool) $this->resource->is_nsfw
            ]
        ]);
    }
}
