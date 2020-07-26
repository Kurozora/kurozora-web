<?php

namespace App\Http\Resources;

use App\ForumReply;
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
        /** @var ForumReply $reply */
        $forumReply = $this->resource;

        $resource = [
            'id'            => $forumReply->id,
            'type'          => 'replies',
            'href'          => route('forum-threads.replies', $forumReply, false),
            'attributes'    => [
                'posted_at' => $forumReply->created_at->format('Y-m-d H:i:s'),
                'poster' => [
                    'id'        => $forumReply->user->id,
                    'username'  => $forumReply->user->username,
                    'avatar'    => $forumReply->user->getFirstMediaFullUrl('avatar')
                ],
                'score'     => $forumReply->likesDiffDislikesCount,
                'content'   => $forumReply->content
            ]
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
