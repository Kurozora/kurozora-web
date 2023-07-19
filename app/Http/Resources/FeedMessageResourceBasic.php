<?php

namespace App\Http\Resources;

use App\Enums\FeedVoteType;
use App\Models\FeedMessage;
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
    public function toArray(Request $request): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        $totalHearts = $feedMessage->viaLoveReactant()->getReactionCounterOfType(FeedVoteType::Heart()->description);

        $user = auth()->user();
        $isReShared = $user && $feedMessage->reShares()->where('user_id', $user->id)->exists();

        $resource = [
            'id'            => (int) $feedMessage->id,
            'uuid'          => (string) $feedMessage->id,
            'type'          => 'feed-messages',
            'href'          => route('api.feed.messages.details', $feedMessage, false),
            'attributes'    => [
                'body'              => $feedMessage->content,
                'content'           => $feedMessage->content,
                'contentHTML'       => $feedMessage->content_html ?? '',
                'contentMarkdown'   => $feedMessage->content_markdown ?? '',
                'metrics'           => [
                    'heartCount'        => $totalHearts->getCount(),
                    'replyCount'        => $feedMessage->replies()->count(),
                    'reShareCount'      => $feedMessage->reShares()->count()
                ],
                'isReply'           => $feedMessage->is_reply,
                'isReShare'         => $feedMessage->is_reshare,
                'isReShared'        => $isReShared,
                'isNSFW'            => $feedMessage->is_nsfw,
                'isSpoiler'         => $feedMessage->is_spoiler,
                'createdAt'         => $feedMessage->created_at->timestamp,
            ]
        ];

        // Add relationships
        $relationships = [];
        $relationships = array_merge($relationships, $this->getUserDetails());

        if (auth()->check()) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());
        }

        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        $user = auth()->user();

        return [
            'isHearted' => $user->getCurrentHeartValueFor($feedMessage) == FeedVoteType::Heart
        ];
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
                'data' => UserResource::collection([$feedMessage->user]),
            ]
        ];
    }
}
