<?php

namespace App\Http\Resources;

use App\Session;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        /** @var Session $session */
        $session = $this->resource;

        return [
            'id'            => $session->id,
            'type'          => 'sessions',
            'href'          => route('api.sessions.details', $session, false),
            'attributes'    => [
                'ip'                => $session->ip,
                'last_validated_at' => $session->last_validated_at->format('Y-m-d H:i:s'),
                'platform'          => [
                    'human_readable_format' => $session->humanReadablePlatform(),
                    'platform'          => $session->platform,
                    'platform_version'  => $session->platform_version,
                    'device_vendor'     => $session->device_vendor,
                    'device_model'      => $session->device_model
                ],
                'location'          => [
                    'city'          => $session->city,
                    'region'        => $session->region,
                    'country'       => $session->country,
                    'latitude'      => $session->latitude,
                    'longitude'     => $session->longitude,
                ]
            ]
        ];
    }
}
