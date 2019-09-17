<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ForumReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = [
            'id'        => $this->id,
            'posted_at' => $this->created_at->format('Y-m-d H:i:s'),
            'poster' => [
                'id'        => $this->user->id,
                'username'  => $this->user->username,
                'avatar'    => $this->user->getFirstMediaFullUrl('avatar')
            ],
            'score'     => $this->likesDiffDislikesCount,
            'content'   => $this->content
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
                'like_action' => $user->likeAction($this->resource)
            ]
        ];
    }
}
