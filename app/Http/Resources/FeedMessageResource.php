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

        // Get basic response
        $resource = FeedMessageResourceBasic::make($feedMessage)->toArray($request);

        // Add relationships
        $relationships = [];
        $relationships = array_merge($relationships, $this->getUserDetails());

        if ($feedMessage->is_reshare != 1)
            $relationships = array_merge($relationships, $this->getReplies());
        else
            $relationships = array_merge($relationships, $this->getReShares());

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
     * Get the re-shares belonging to the re-shared feed message filtered by the current user's ID.
     *
     * @return array
     */
    private function getReShares(): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        return [
            'messages' => [
                'data' => FeedMessageResourceBasic::collection(
                   $feedMessage->reShares->where('user_id', '=', $feedMessage->user_id)
                )
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
