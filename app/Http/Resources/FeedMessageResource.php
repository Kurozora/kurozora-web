<?php

namespace App\Http\Resources;

use App\Models\FeedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedMessageResource extends JsonResource
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

        // Get basic response
        $resource = FeedMessageResourceBasic::make($feedMessage)->toArray($request);

        // Add relationships
        $relationships = $resource['relationships'];

        if ($feedMessage->is_reshare || $feedMessage->is_reply) {
            $relationships = array_merge($relationships, $this->getParentMessage());
        }

        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Get the parent message to which the current message belongs.
     *
     * @return array
     */
    private function getParentMessage(): array
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        return [
            'parent' => [
                'data' => FeedMessageResource::collection([$feedMessage->parentMessage])
            ]
        ];
    }

    /**
     * Get the re-shares belonging to the feed message.
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
                   $feedMessage->reShares()
                       ->orderByDesc('created_at')
                       ->paginate(25)
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
                'data' => FeedMessageResource::collection(
                    $feedMessage->replies()
                        ->orderByDesc('created_at')
                        ->paginate(25))
            ]
        ];
    }
}
