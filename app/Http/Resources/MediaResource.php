<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Media $resource
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
        return [
            'url'               => $this->resource->getFullUrl(),
            'height'            => $this->resource->getCustomProperty('height'),
            'width'             => $this->resource->getCustomProperty('width'),
            'backgroundColor'   => $this->resource->getCustomProperty('background_color'),
            'textColor1'        => $this->resource->getCustomProperty('text_color_1'),
            'textColor2'        => $this->resource->getCustomProperty('text_color_2'),
            'textColor3'        => $this->resource->getCustomProperty('text_color_3'),
            'textColor4'        => $this->resource->getCustomProperty('text_color_4'),
        ];
    }
}
