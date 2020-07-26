<?php

namespace App\Http\Resources;

use App\ForumThread;
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
        /** @var ForumThread $forumThread */
        $forumThread = $this->resource;

        $resource = [
            'id'            => $forumThread->id,
            'type'          => 'threads',
            'href'          => route('forum-threads.details', $forumThread, false),
            'attributes'    => [
                'title'             => $forumThread->title,
                'content'           => $forumThread->content,
                'locked'            => (bool) $forumThread->locked,
                'poster_user_id'    => $forumThread->user->id,
                'poster_username'   => $forumThread->user->username,
                'creation_date'     => $forumThread->created_at->format('Y-m-d H:i:s'),
                'reply_count'       => $forumThread->replies->count(),
                'score'             => $forumThread->likesDiffDislikesCount
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
