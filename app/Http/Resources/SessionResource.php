<?php

namespace App\Http\Resources;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
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
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'id'            => $this->resource->id,
            'uuid'          => (string) $this->resource->id,
            'type'          => 'sessions',
            'href'          => route('api.me.sessions.details', $this->resource, false),
            'attributes'    => [
                'ipAddress'         => $this->resource->ip_address,
                'lastValidatedAt'   => $this->resource->last_activity,
            ]
        ];

        // Add additional data to the resource
        $relationships = [
            'relationships' => [
                'platform' => [
                    'data' => PlatformResource::collection([$this->resource->session_attribute])
                ],
                'location' => [
                    'data' => LocationResource::collection([$this->resource->session_attribute])
                ]
            ]
        ];

        return array_merge($resource, $relationships);
    }
}
