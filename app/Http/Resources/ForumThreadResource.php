<?php

namespace App\Http\Resources;

use App\Enums\VoteType;
use App\ForumThread;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ForumThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws InvalidEnumKeyException
     */
    public function toArray($request): array
    {
        /** @var ForumThread $forumThread */
        $forumThread = $this->resource;

        $totalReactions = $forumThread->viaLoveReactant()->getReactionTotal();
        $totalLikes = $forumThread->viaLoveReactant()->getReactionCounterOfType(VoteType::Like()->description);
        $totalDislikes = $forumThread->viaLoveReactant()->getReactionCounterOfType(VoteType::Dislike()->description);

        $resource = [
            'id'            => $forumThread->id,
            'type'          => 'threads',
            'href'          => route('api.forum-threads.details', $forumThread, false),
            'attributes'    => [
                'title'         => $forumThread->title,
                'content'       => $forumThread->content,
                'isLocked'      => (bool) $forumThread->locked,
                'replyCount'    => $forumThread->replies->count(),
                'metrics'       => [
                    'count'     => $totalReactions->getCount(),
                    'weight'    => $totalReactions->getWeight(),
                    'likes'     => $totalLikes->getCount(),
                    'dislikes'  => $totalDislikes->getCount()
                ],
                'createdAt'     => $forumThread->created_at->format('Y-m-d H:i:s'),
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
     * @throws InvalidEnumKeyException
     */
    protected function getUserSpecificDetails(): array
    {
        $user = Auth::user();

        return [
            'currentUser' => [
                'voteAction' => $user->getCurrentVoteValue()
            ]
        ];
    }

    /**
     * Returns the poster relationship for the resource.
     *
     * @return array
     */
    protected function getPosterRelationship(): array
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
