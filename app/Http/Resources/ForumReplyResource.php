<?php

namespace App\Http\Resources;

use App\Enums\ForumsVoteType;
use App\Models\ForumReply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ForumReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ForumReply $reply */
        $forumReply = $this->resource;

        $totalReactions = $forumReply->viaLoveReactant()->getReactionTotal();
        $totalLikes = $forumReply->viaLoveReactant()->getReactionCounterOfType(ForumsVoteType::Like()->description);
        $totalDislikes = $forumReply->viaLoveReactant()->getReactionCounterOfType(ForumsVoteType::Dislike()->description);

        $resource = [
            'id'            => $forumReply->id,
            'type'          => 'replies',
            'href'          => route('api.forum-threads.replies', $forumReply, false),
            'attributes'    => [
                'content'       => $forumReply->content,
                'metrics'       => [
                    'count'     => $totalReactions->getCount(),
                    'weight'    => $totalReactions->getWeight(),
                    'likes'     => $totalLikes->getCount(),
                    'dislikes'  => $totalDislikes->getCount()
                ],
                'voteAction'    => 0,
                'createdAt'     => $forumReply->created_at->format('Y-m-d H:i:s'),
            ]
        ];

        $relationships = [];
        $relationships = array_merge($relationships, $this->getPosterRelationship());
        $resource = array_merge($resource, ['relationships' => $relationships]);

        if (Auth::check())
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        /** @var ForumReply $forumReply */
        $forumReply = $this->resource;

        $user = Auth::user();

        return [
            'voteAction' => $user->getCurrentVoteValueFor($forumReply)
        ];
    }

    /**
     * Returns the poster relationship for the resource.
     *
     * @return array
     */
    protected function getPosterRelationship(): array
    {
        /** @param ForumReply $forumReply */
        $forumReply = $this->resource;

        return [
            'users' => [
                'data' => UserResourceBasic::collection([$forumReply->user]),
            ]
        ];
    }
}
