<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResourceSmall extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        $resource = [
            'id'                => $this->id,
            'username'          => $this->username,
            'biography'         => $this->biography,
            'avatar_url'        => $this->getFirstMediaFullUrl('avatar'),
            'banner_url'        => $this->getFirstMediaFullUrl('banner'),
            'follower_count'    => $this->getFollowerCount(),
            'following_count'   => $this->getFollowingCount(),
            'reputation_count'  => $this->getReputationCount()
        ];

        if(Auth::check())
            $resource = array_merge($resource, $this->getUserSpecificDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails() {
        $user = Auth::user();

        return [
            'current_user' => [
                'following' => $this->resource->followers()->where('user_id', $user->id)->exists()
            ]
        ];
    }
}
