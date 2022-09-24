<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var User $resource
     */
    public $resource;

    /**
     * Whether to include private details in the resource.
     *
     * @var bool $includePrivateDetails
     */
    private bool $includePrivateDetails = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = UserResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'id'                => $this->resource->id,
            'type'              => 'users',
            'href'              => route('api.users.profile', $this->resource, false),
            'attributes'        => [
                'slug'              => $this->resource->slug,
                'profile'           => ImageResource::make($this->resource->profile_image),
                'banner'            => ImageResource::make($this->resource->banner_image),
                'username'          => $this->resource->username,
                'biography'         => $this->resource->biography,
                'activityStatus'    => $this->resource->getActivityStatus()->description,
                'followerCount'     => $this->resource->followers()->count(),
                'followingCount'    => $this->resource->following()->count(),
                'reputationCount'   => $this->resource->getReputationCount(),
                'joinDate'          => $this->resource->created_at->timestamp,
                'isPro'             => $this->resource->isPro(),
            ]
        ]);

        if (auth()->check()) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());
        }

        if ($this->includePrivateDetails) {
            $resource = array_merge($resource, $this->getPrivateDetails());
        }

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        $followedUser = $this->resource;
        $user = auth()->user();

        $isFollowed = null;
        if ($followedUser->id != $user->id) {
            $isFollowed = $this->resource->followers()->where('user_id', $user->id)->exists();
        }

        return [
            'isFollowed' => $isFollowed
        ];
    }

    /**
     * Returns private information of the resource.
     *
     * @return array
     */
    protected function getPrivateDetails(): array
    {
        return [
            'private' => [
                'settings' => $this->resource->settings,
            ]
        ];
    }

    /**
     * Enables including private details in the resource.
     *
     * @return UserResourceBasic
     */
    function includePrivateDetails(): self
    {
        $this->includePrivateDetails = true;
        return $this;
    }
}
