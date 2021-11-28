<?php

namespace App\Http\Resources;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Session $resource
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
            'type'          => 'locations',
            'attributes'    => [
                'city'          => $this->resource->city,
                'region'        => $this->resource->region,
                'country'       => $this->resource->country,
                'latitude'      => $this->resource->latitude,
                'longitude'     => $this->resource->longitude
            ]
        ];
    }
}
