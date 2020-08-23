<?php

namespace App\Http\Resources;

use App\AnimeImages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @param AnimeImages $animeImage */
        $animeImage = $this->resource;

        return [
            'url'               => $animeImage->url,
            'height'            => $animeImage->height,
            'width'             => $animeImage->width,
            'backgroundColor'   => $animeImage->background_color,
            'textColor1'        => $animeImage->text_color_1,
            'textColor2'        => $animeImage->text_color_2,
            'textColor3'        => $animeImage->text_color_3,
            'textColor4'        => $animeImage->text_color_4,
        ];
    }
}
