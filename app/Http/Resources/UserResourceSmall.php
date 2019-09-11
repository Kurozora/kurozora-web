<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceSmall extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'username'          => $this->username,
            'biography'         => $this->biography,
            'avatar_url'        => $this->getAvatarURL(),
            'banner_url'        => $this->banner,
            'follower_count'    => $this->getFollowerCount(),
            'following_count'   => $this->getFollowingCount(),
            'reputation_count'  => $this->getReputationCount()
        ];
    }
}
