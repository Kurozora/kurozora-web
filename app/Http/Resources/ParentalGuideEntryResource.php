<?php

namespace App\Http\Resources;

use App\Enums\ParentalGuideReaction;
use App\Models\ParentalGuideEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentalGuideEntryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var ParentalGuideEntry $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = ParentalGuideEntryResourceIdentity::make($this->resource)->toArray($request);

        $resource['attributes'] = [
            'category' => (int) $this->resource->category->value,
            'rating' => (int) $this->resource->rating->value,
            'frequency' => $this->resource->frequency?->value,
            'depiction' => $this->resource->depiction?->value,
            'reason' => $this->resource->reason,
            'isSpoiler' => (bool) $this->resource->is_spoiler,
            'helpfulCount' => $this->resource->helpful_count,
            'unhelpfulCount' => $this->resource->unhelpful_count,
            'isHelpful' => $this->resolveIsHelpful(),
            'userID' => (string) $this->resource->user_id,
            'createdAt' => $this->resource->created_at?->timestamp,
            'updatedAt' => $this->resource->updated_at?->timestamp,
        ];

        return $resource;
    }

    /**
     * Resolve the authenticated user's helpful state on the entry.
     *
     * @return bool|null
     */
    private function resolveIsHelpful(): ?bool
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        $reaction = $user->getHelpfulnessFor($this->resource);

        return $reaction?->is(ParentalGuideReaction::Helpful);

    }
}
