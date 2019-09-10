<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ForumThreadResource extends JsonResource
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
            'id'                => $this->id,
            'title'             => $this->title,
            'content'           => $this->content,
            'locked'            => (bool) $this->locked,
            'poster_user_id'    => $this->user->id,
            'poster_username'   => $this->user->username,
            'creation_date'     => $this->created_at->format('Y-m-d H:i:s'),
            'reply_count'       => $this->replies->count(),
            'score'             => $this->likesDiffDislikesCount
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
