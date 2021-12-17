<?php

namespace App\Http\Resources;

use App\Models\MediaStat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaStatsResource extends JsonResource
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
                round($this->resource->rating_1, 1),
                round($this->resource->rating_2, 1),
                round($this->resource->rating_3, 1),
                round($this->resource->rating_4, 1),
                round($this->resource->rating_5, 1),
                round($this->resource->rating_6, 1),
                round($this->resource->rating_7, 1),
                round($this->resource->rating_8, 1),
                round($this->resource->rating_9, 1),
                round($this->resource->rating_10, 1),
            ],
            'ratingAverage' => round($this->resource->rating_average, 1),
            'ratingCount' => $this->resource->rating_count,
        ];
    }
}
