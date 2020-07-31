<?php

namespace App\Http\Resources;

use App\User;
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        /** @var User $user */
        $user = $this->resource;

        $resource = [
            'id'                => $user->id,
            'type'              => 'users',
            'href'              => route('api.users.profile', $user, false),
            'attributes'        => [
                'username'          => $user->username,
                'activity_status'   => $user->getActivityStatus()->description,
                'biography'         => $user->biography,
                'avatar_url'        => $user->getFirstMediaFullUrl('avatar'),
                'banner_url'        => $user->getFirstMediaFullUrl('banner'),
                'follower_count'    => $user->getFollowerCount(),
                'following_count'   => $user->getFollowingCount(),
                'reputation_count'  => $user->getReputationCount()
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
    protected function getUserSpecificDetails()
    {
        $user = Auth::user();

        return [
            'current_user' => [
                'following' => $this->resource->followers()->where('user_id', $user->id)->exists()
            ]
        ];
    }

    /**
     * Returns private information of the resource.
     *
     * @return array
     */
    protected function getPrivateDetails()
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'private' => [
                'username_change_available' => $user->username_change_available,
            ]
        ];
    }

    /**
     * Enables including private details in the resource.
     *
     * @return $this
     */
    function includePrivateDetails()
    {
        $this->includePrivateDetails = true;
        return $this;
    }
}
