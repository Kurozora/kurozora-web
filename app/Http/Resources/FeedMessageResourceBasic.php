<?php

namespace App\Http\Resources;

use App\Enums\FeedVoteType;
use App\Models\FeedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedMessageResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var FeedMessage $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $totalHearts = $this->resource->viaLoveReactant()->getReactionCounterOfType(FeedVoteType::Heart()->description);

        $resource = [
            'id'            => (int) $this->resource->id,
            'uuid'          => (string) $this->resource->id,
            'type'          => 'feed-messages',
            'href'          => route('api.feed.messages.details', $this->resource, false),
            'attributes'    => [
                'body'              => $this->resource->content,
                'content'           => $this->resource->content,
                'contentHTML'       => $this->resource->content_html ?? '',
                'contentMarkdown'   => $this->resource->content_markdown ?? '',
                'metrics'           => [
                    'heartCount'        => $totalHearts->getCount(),
                    'replyCount'        => $this->resource->replies_count,
                    'reShareCount'      => $this->resource->re_shares_count
                ],
                'isReply'           => $this->resource->is_reply,
                'isReShare'         => $this->resource->is_reshare,
                'isReShared'        => (bool) $this->resource->isReShared,
                'isNSFW'            => $this->resource->is_nsfw,
                'isSpoiler'         => $this->resource->is_spoiler,
                'createdAt'         => $this->resource->created_at->timestamp,
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
        $user = auth()->user();

        return [
            'isHearted' => $user->getCurrentHeartValueFor($this->resource) == FeedVoteType::Heart
        ];
    }

    /**
     * Get the user details belonging to the feed message.
     *
     * @return array
     */
    private function getUserDetails(): array
    {
        return [
            'users' => [
                'data' => UserResource::collection([$this->resource->user]),
            ]
        ];
    }
}
