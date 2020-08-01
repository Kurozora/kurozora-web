<?php

namespace App\Http\Resources;

use App\Helpers\KuroAuthToken;
use App\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Whether or not to include authentication key in the resource.
     *
     * @var bool $includesAuthKey
     */
    private $shouldIncludesAuthKey = false;

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

        $resource = SessionResourceBasic::make($session)->toArray($request);

        // Add additional data to the resource
        $relationships = [
            'relationships' => [
                'user' => [
                    'data' => UserResourceBasic::collection([$session->user])
                ]
            ]
        ];

        if($this->shouldIncludesAuthKey)
            $resource = array_merge($resource, $this->getAuthenticationKey());

        return array_merge($resource, $relationships);
    }

    /**
     * Returns the authentication key of the authenticated user.
     *
     * @return array
     */
    protected function getAuthenticationKey(): array
    {
        /** @var Session $session */
        $session = $this->resource;

        return [
            'auth_token'   => KuroAuthToken::generate($session->user->id, $session->secret)
        ];
    }

    /**
     * Enables including authentication key in the resource.
     *
     * @return SessionResource
     */
    public function includesAuthKey(): self
    {
        $this->shouldIncludesAuthKey = true;
        return $this;
    }
}
