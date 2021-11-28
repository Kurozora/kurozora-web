<?php

namespace App\Http\Resources;

use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var User $resource
     */
    public $resource;

    /**
     * Whether to include the given session in the resource.
     *
     * @var bool $shouldIncludeSession
     */
    private bool $shouldIncludeSession = false;

    /** @var Session $session */
    private Session $session;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = UserResourceBasic::make($this->resource)->toArray($request);

        // Add additional data to the resource
        $relationships = [];

        $relationships = array_merge($relationships, $this->getBadgeRelationship());

        if ($this->shouldIncludeSession) {
            $resource['attributes'] = array_merge($resource['attributes'], [
                'email'         => $this->resource->email,
                'siwaIsEnabled' => !empty($this->resource->siwa_id)
            ]);
            $relationships = array_merge($relationships, $this->getSessionRelationship());
        }

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the badges relationship for the resource.
     *
     * @return array
     */
    protected function getBadgeRelationship(): array
    {
        return [
            'badges' => [
                'data' => BadgeResource::collection($this->resource->badges)
            ]
        ];
    }

    /**
     * Returns the sessions relationship for the resource.
     *
     * @return array
     */
    protected function getSessionRelationship(): array
    {
        return [
            'sessions' => [
                'data' => SessionResource::collection([$this->session])
            ]
        ];
    }

    /**
     * Enables including the given session in the resource.
     *
     * @param Session $session
     * @return $this
     */
    public function includingSession(Session $session): self
    {
        $this->session = $session;
        $this->shouldIncludeSession = true;
        return $this;
    }
}
