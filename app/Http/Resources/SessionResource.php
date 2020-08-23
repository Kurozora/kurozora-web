<?php

namespace App\Http\Resources;

use App\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Session $session */
        $session = $this->resource;

        $resource = [
            'id'            => $session->id,
            'type'          => 'sessions',
            'href'          => route('api.me.sessions.details', $session, false),
            'attributes'    => [
                'ip'                => $session->ip,
                'lastValidatedAt'   => $session->last_validated_at->format('Y-m-d H:i:s'),
            ]
        ];

        // Add additional data to the resource
        $relationships = [
            'relationships' => [
                'platform' => [
                    'data' => PlatformResource::collection([$session])
                ],
                'location' => [
                    'data' => LocationResource::collection([$session])
                ]
            ]
        ];

        return array_merge($resource, $relationships);
    }
}
