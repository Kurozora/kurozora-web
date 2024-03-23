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
        return array_merge($resource, [
            'attributes' => [
                'type' => mb_strtolower(class_basename($this->resource->model_type)),
                'score' => $this->resource->rating,
                'description' => $this->resource->description,
                'createdAt' => $this->resource->created_at->timestamp
            ]
        ]);
    }
}
