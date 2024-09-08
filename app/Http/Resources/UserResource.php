<?php

namespace App\Http\Resources;

use App\Models\PersonalAccessToken;
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

    /**
     * Whether to include user's settings in the resource.
     *
     * @var bool $shouldIncludeSettings
     */
    private bool $shouldIncludeSettings = false;

    /** @var PersonalAccessToken $personalAccessToken */
    private PersonalAccessToken $personalAccessToken;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = UserResourceBasic::make($this->resource)
            ->includingSettings($this->shouldIncludeSettings)
            ->toArray($request);

        // Add additional data to the resource
        $relationships = [];

        $relationships = array_merge($relationships, $this->getAchievementRelationship());

        if ($this->shouldIncludeSession) {
            $resource['attributes'] = array_merge($resource['attributes'], [
                'email'         => $this->resource->email,
                'siwaIsEnabled' => !empty($this->resource->siwa_id)
            ]);
            $relationships = array_merge($relationships, $this->getAccessTokensRelationship());
        }

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the achievements relationship for the resource.
     *
     * @return array
     */
    protected function getAchievementRelationship(): array
    {
        $achievements = AchievementResource::collection($this->resource->badges);
        return [
            'badges' => [ // TODO: - Remove after 1.10.0
                'data' => $achievements
            ],
            'achievements' => [
                'data' => $achievements
            ]
        ];
    }

    /**
     * Returns the access tokens relationship for the resource.
     *
     * @return array
     */
    protected function getAccessTokensRelationship(): array
    {
        return [
            'accessTokens' => [
                'data' => AccessTokenResource::collection([$this->personalAccessToken])
            ]
        ];
    }

    /**
     * Enables including the given session in the resource.
     *
     * @param PersonalAccessToken $personalAccessToken
     * @return $this
     */
    public function includingAccessToken(PersonalAccessToken $personalAccessToken): self
    {
        $this->personalAccessToken = $personalAccessToken;
        $this->shouldIncludeSession = true;
        $this->shouldIncludeSettings = true;
        return $this;
    }
}
