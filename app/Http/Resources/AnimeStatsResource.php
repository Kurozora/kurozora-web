<?php

namespace App\Http\Resources;

use App\Models\MediaStat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStatsResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaStat $resource
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
            'ratingCountList' => [
                $this->resource->rating_1,
                $this->resource->rating_2,
                $this->resource->rating_3,
                $this->resource->rating_4,
                $this->resource->rating_5,
                $this->resource->rating_6,
                $this->resource->rating_7,
                $this->resource->rating_8,
                $this->resource->rating_9,
                $this->resource->rating_10,
            ],
            'ratingAverage' => $this->resource->rating_average,
            'ratingCount' => $this->resource->rating_count,
        ];
    }
}
