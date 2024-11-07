<?php

namespace App\Http\Resources;

use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessTokenResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var PersonalAccessToken $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'id' => (string) $this->resource->id,
            'type' => 'access-token',
            'href' => route('api.me.access-tokens.details', $this->resource, false),
            'attributes' => [
                'ipAddress' => $this->resource->session_attribute->ip_address,
                'lastValidatedAt' => $this->resource->last_used_at?->timestamp,
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
