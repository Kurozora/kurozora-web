<?php

namespace App\Http\Resources;

use App\Enums\FeedVoteType;
use App\FeedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
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
            'attribute'     => [
                'body'      => $feedMessage->body,
                'replyCount'    => $feedMessage->replies->count(),
                'metrics'       => [
                    'count'     => $totalReactions->getCount(),
                    'weight'    => $totalReactions->getWeight(),
                    'hearts'    => $totalHearts->getCount()
                ],
                'isNSFW'    => $feedMessage->is_nsfw,
                'isSpoiler' => $feedMessage->is_spoiler,
                'createdAt' => $feedMessage->created_at->format('Y-m-d H:i:s'),
            ]
        ];

        $relationships = [];
        $relationships = array_merge($relationships, $this->getUserDetails());
        $relationships = array_merge($relationships, $this->getReplies());

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

    /**
     * Get the replies belonging to the feed message.
     *
     * @return array
     */
    private function getReplies(): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        return [
            'messages' => [
                'data' => FeedMessageResource::collection($feedMessage->replies)
            ]
        ];
    }
}
