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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     *
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
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
                'title'         => $forumThread->title,
                'content'       => $forumThread->content,
                'locked'        => (bool) $forumThread->locked,
                'reply_count'   => $forumThread->replies->count(),
                'score'         => $forumThread->viaLoveReactant()->getReactionTotal()->getCount(),
                'created_at'    => $forumThread->created_at,
            ]
        ];

        $relationships = [];
        $relationships = array_merge($relationships, $this->getPosterRelationship());
        $resource = array_merge($resource, ['relationships' => $relationships]);

        if(Auth::check())
            $resource = array_merge($resource, $this->getUserSpecificDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     *
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    protected function getUserSpecificDetails() {
        $user = Auth::user();

        return [
            'current_user' => [
                'vote_action' => $user->getCurrentVoteValueFor($this->resource)
            ]
        ];
    }

    /**
     * Returns the poster relationship for the resource.
     *
     * @return array
     */
    protected function getPosterRelationship()
    {
        /** @param ForumThread $forumThread */
        $forumThread = $this->resource;

        return [
            'user' => [
                'data' => UserResourceBasic::collection([$forumThread->user]),
            ]
        ];
    }
}
