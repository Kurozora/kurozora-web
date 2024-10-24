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
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = GenreResourceIdentity::make($this->resource)->toArray($request);

        return array_merge($resource, [
            'attributes'    => [
                'slug'              => $this->resource->slug,
                'name'              => $this->resource->name,
                'color'             => $this->resource->background_color_1,
                'backgroundColor1'  => $this->resource->background_color_1,
                'backgroundColor2'  => $this->resource->background_color_2,
                'textColor1'        => $this->resource->text_color_1,
                'textColor2'        => $this->resource->text_color_2,
                'symbol'            => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Symbol)),
                'description'       => $this->resource->description,
                'isNSFW'            => (bool) $this->resource->is_nsfw
            ]
        ]);
    }
}
