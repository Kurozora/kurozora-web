<?php

namespace App\Http\Resources;

use App\Models\MediaRating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRatingResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaRating $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = MediaRatingResourceIdentity::make($this->resource)->toArray($request);
        $isOwnRating = auth()->id() === $this->resource->user_id;

        return array_merge($resource, [
            'attributes' => [
                'score' => $this->resource->rating,
                'description' => $this->resource->description,
                'note' => $isOwnRating ? $this->resource->note : null,
                'isSpoiler' => (bool) $this->resource->is_spoiler,
                'createdAt' => $this->resource->created_at->timestamp
            ]
        ]);
    }
}
