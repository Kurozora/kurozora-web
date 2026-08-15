<?php

namespace App\Http\Resources;

use App\Models\RatingCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingCategoryResourceIdentity extends JsonResource
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
        return [
            'id' => (string) $this->resource->id,
            'type' => 'rating-categories',
            'href' => '',
        ];
    }
}
