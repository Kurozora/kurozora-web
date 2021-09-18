<?php

namespace App\Http\Resources;

use App\Models\User;
use Auth;
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
        $user = $this->resource;

        $resource = [
            'id'                => $user->id,
            'type'              => 'users',
            'href'              => route('api.users.profile', $user, false),
            'attributes'        => [
                'profile'           => ImageResource::make($user->profile_image),
                'banner'            => ImageResource::make($user->banner_image),
                'username'          => $user->username,
                'biography'         => $user->biography,
                'activityStatus'    => $user->getActivityStatus()->description,
                'followerCount'     => $user->getFollowerCount(),
                'followingCount'    => $user->getFollowingCount(),
                'reputationCount'   => $user->getReputationCount(),
                'joinDate'          => $user->created_at->format('Y-m-d'),
                'isPro'             => $user->isPro(),
            ]
        ];

        if (Auth::check()) {
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
        $user = Auth::user();

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
        $user = $this->resource;

        return [
            'private' => [
                'settings' => $user->settings,
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
