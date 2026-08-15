<?php

namespace App\Http\Resources;

use App\Models\RatingCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingCategoryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var RatingCategory $resource
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
        $resource = RatingCategoryResourceIdentity::make($this->resource)->toArray($request);

        return array_merge($resource, [
            'attributes' => [
                'slug' => $this->resource->slug,
                'name' => $this->resource->name,
                'description' => $this->resource->description,
                'weight' => (float) $this->resource->weight,
                'displayOrder' => (int) $this->resource->display_order,
                'score' => $this->resource->user_score !== null ? (float) $this->resource->user_score : null,
                'review' => $this->resource->user_review,
            ],
        ]);
    }
}
