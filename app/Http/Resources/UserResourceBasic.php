<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResourceBasic extends JsonResource
{
    /**
     * Whether or not to include private details in the resource.
     *
     * @var bool $includePrivateDetails
     */
    private $includePrivateDetails = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this->resource;

        $resource = [
            'id'                => $user->id,
            'type'              => 'users',
            'href'              => route('api.users.profile', $user, false),
            'attributes'        => [
                'username'          => $user->username,
                'activityStatus'    => $user->getActivityStatus()->description,
                'biography'         => $user->biography,
                'avatarUrl'         => $user->getFirstMediaFullUrl('avatar'),
                'bannerUrl'         => $user->getFirstMediaFullUrl('banner'),
                'followerCount'     => $user->getFollowerCount(),
                'followingCount'    => $user->getFollowingCount(),
                'reputationCount'   => $user->getReputationCount()
            ]
        ];

        if(Auth::check())
            $resource = array_merge($resource, $this->getUserSpecificDetails());

        if($this->includePrivateDetails)
            $resource = array_merge($resource, $this->getPrivateDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        $user = Auth::user();

        return [
            'currentUser' => [
                'following' => $this->resource->followers()->where('user_id', $user->id)->exists()
            ]
        ];
    }

    /**
     * Returns private information of the resource.
     *
     * @return array
     */
    protected function getPrivateDetails(): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'private' => [
                'usernameChangeAvailable' => $user->username_change_available,
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
