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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     *
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
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
                'content'   => $forumReply->content,
                'score'     => $forumReply->viaLoveReactant()->getReactionTotal()->getCount(),
                'created_at' => $forumReply->created_at,
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
                'like_action' => $user->getCurrentVoteValueFor($this->resource)
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
        /** @param ForumReply $forumReply */
        $forumReply = $this->resource;

        return [
            'user' => [
                'data' => UserResourceBasic::collection([$forumReply->user]),
            ]
        ];
    }
}
