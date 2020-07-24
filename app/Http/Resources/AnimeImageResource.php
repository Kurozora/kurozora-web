<?php

namespace App\Http\Resources;

use App\AnimeImages;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @param AnimeImages $animeImage */
        $animeImage = $this->resource;

        return [
            'url'               => $animeImage->url,
            'height'            => $animeImage->height,
            'width'             => $animeImage->width,
            'background_color'  => $animeImage->background_color,
            'text_color_1'      => $animeImage->text_color_1,
            'text_color_2'      => $animeImage->text_color_2,
            'text_color_3'      => $animeImage->text_color_3,
            'text_color_4'      => $animeImage->text_color_4,
        ];
    }
}
