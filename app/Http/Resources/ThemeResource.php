<?php

namespace App\Http\Resources;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Theme $resource
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
        $resource = ThemeResourceIdentity::make($this->resource)->toArray($request);

        return array_merge($resource, [
            'attributes'    => [
                'slug'          => $this->resource->slug,
                'name'          => $this->resource->name,
                'color'         => $this->resource->color,
                'symbol'        => ImageResource::make($this->resource->symbol_image),
                'description'   => $this->resource->description,
                'isNSFW'        => (bool) $this->resource->is_nsfw
            ]
        ]);
    }
}
