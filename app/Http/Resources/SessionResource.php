<?php

namespace App\Http\Resources;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
    public function toArray($request): array
    {
        $resource = [
            'id'            => $this->resource->id,
            'type'          => 'sessions',
            'href'          => route('api.me.sessions.details', $this->resource, false),
            'attributes'    => [
                'ipAddress'         => $this->resource->ip_address,
                'lastValidatedAt'   => Carbon::createFromTimestamp($this->resource->last_activity)->format('Y-m-d H:i:s'),
            ]
        ];

        // Add additional data to the resource
        $relationships = [
            'relationships' => [
                'platform' => [
                    'data' => PlatformResource::collection([$this->resource])
                ],
                'location' => [
                    'data' => LocationResource::collection([$this->resource])
                ]
            ]
        ];

        return array_merge($resource, $relationships);
    }
}
