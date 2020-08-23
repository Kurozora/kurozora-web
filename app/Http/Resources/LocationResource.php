<?php

namespace App\Http\Resources;

use App\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Session $session */
        $session = $this->resource;

        return [
            'type'          => 'locations',
            'attributes'    => [
                'city'          => $session->city,
                'region'        => $session->region,
                'country'       => $session->country,
                'latitude'      => $session->latitude,
                'longitude'     => $session->longitude
            ]
        ];
    }
}
