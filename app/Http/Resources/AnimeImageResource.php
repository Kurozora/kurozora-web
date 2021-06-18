<?php

namespace App\Http\Resources;

use App\Models\AnimeImages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeImageResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var AnimeImages $resource
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
            'url'               => $this->resource->url,
            'height'            => $this->resource->height,
            'width'             => $this->resource->width,
            'backgroundColor'   => $this->resource->background_color,
            'textColor1'        => $this->resource->text_color_1,
            'textColor2'        => $this->resource->text_color_2,
            'textColor3'        => $this->resource->text_color_3,
            'textColor4'        => $this->resource->text_color_4,
        ];
    }
}
