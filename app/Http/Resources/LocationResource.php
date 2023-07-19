<?php

namespace App\Http\Resources;

use App\Models\SessionAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var SessionAttribute $resource
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
            'type'          => 'locations',
            'attributes'    => [
                'city'          => $this->resource?->city ?? 'Unknown',
                'region'        => $this->resource?->region ?? 'Unknown',
                'country'       => $this->resource?->country ?? 'Unknown',
                'latitude'      => $this->resource?->latitude ?? 0,
                'longitude'     => $this->resource?->longitude ?? 0,
            ]
        ];
    }
}
