<?php

namespace App\Http\Resources;

use App\Models\Recap;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Recap $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = RecapResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes' => [
                'year'                  => $this->resource->year,
                'type'                  => class_basename($this->resource->type),
                'totalSeriesCount'      => $this->resource->total_series_count,
                'totalPartsCount'       => $this->resource->total_parts_count,
                'totalPartsDuration'    => $this->resource->total_parts_duration,
                'topPercentile'         => $this->resource->top_percentile,
            ]
        ]);
        return $resource;
    }
}
