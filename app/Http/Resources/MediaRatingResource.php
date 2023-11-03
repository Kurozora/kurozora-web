<?php

namespace App\Http\Resources;

use App\Models\MediaRating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRatingResource extends JsonResource
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
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = MediaRatingResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes'    => [
                'score' => $this->resource->rating,
                'description' => $this->resource->description,
                'createdAt' => $this->resource->created_at->timestamp
            ]
        ]);

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
        $mediaRating = $this->resource;

        return [
            'users' => [
                'data' => UserResourceBasic::collection([$mediaRating->user]),
            ]
        ];
    }
}
