<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Badge $resource
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
            'type'          => 'badges',
            'attributes'    => [
                'name'              => $this->resource->name,
                'description'       => $this->resource->description,
                'textColor'         => $this->resource->text_color,
                'backgroundColor'   => $this->resource->background_color,
                'symbol'            => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Symbol)),
            ]
        ];
    }
}
