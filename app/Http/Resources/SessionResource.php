<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'device'            => $this->device,
            'ip'                => $this->ip,
            'last_validated'    => $this->formatLastValidated(),
            'location'          => [
                'city'          => $this->city,
                'region'        => $this->region,
                'country'       => $this->country,
                'latitude'      => $this->latitude,
                'longitude'     => $this->longitude,
            ]
        ];
    }
}
