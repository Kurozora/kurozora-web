<?php

namespace App\Http\Resources;

use App\Models\FeedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedMessageResource extends JsonResource
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
        // Get basic response
        $resource = FeedMessageResourceBasic::make($this->resource)->toArray($request);

        // Add relationships
        $relationships = $resource['relationships'];

        if ($this->resource->is_reshare || $this->resource->is_reply) {
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
        return [
            'parent' => [
                'data' => FeedMessageResourceBasic::collection([$this->resource->parentMessage])
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
        return [
            'messages' => [
                'data' => FeedMessageResourceBasic::collection(
                   $this->resource->reShares()
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
        return [
            'messages' => [
                'data' => FeedMessageResource::collection(
                    $this->resource->replies()
                        ->orderByDesc('created_at')
                        ->paginate(25))
            ]
        ];
    }
}
