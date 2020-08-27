<?php

namespace App\Http\Resources;

use App\Enums\FeedVoteType;
use App\FeedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedMessageResourceBasic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        $totalReactions = $feedMessage->viaLoveReactant()->getReactionTotal();
        $totalHearts = $feedMessage->viaLoveReactant()->getReactionCounterOfType(FeedVoteType::Heart()->description);

        $resource = [
            'id'            => $feedMessage->id,
            'type'          => 'feed-messages',
            'href'          => route('api.feed.messages.details', $feedMessage, false),
            'attributes'    => [
                'body'          => $feedMessage->body,
                'replyCount'    => $feedMessage->replies->count(),
                'reShareCount'  => $feedMessage->reShares->count(),
                'metrics'       => [
                    'count'     => $totalReactions->getCount(),
                    'weight'    => $totalReactions->getWeight(),
                    'hearts'    => $totalHearts->getCount()
                ],
                'isReply'       => $feedMessage->is_reply == 1,
                'isReShare'     => $feedMessage->is_reshare == 1,
                'isNSFW'        => $feedMessage->is_nsfw == 1,
                'isSpoiler'     => $feedMessage->is_spoiler == 1,
                'createdAt'     => $feedMessage->created_at->format('Y-m-d H:i:s'),
            ]
        ];

        // Add relationships
        $relationships = [];
        $relationships = array_merge($relationships, $this->getUserDetails());

        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Get the user details belonging to the feed message.
     *
     * @return array
     */
    private function getUserDetails(): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        return [
            'users' => [
                'data' => UserResourceBasic::collection([$feedMessage->user]),
            ]
        ];
    }
}
