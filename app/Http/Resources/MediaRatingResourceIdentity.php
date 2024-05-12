<?php

namespace App\Http\Resources;

use App\Models\MediaRating;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class MediaRatingResourceIdentity extends JsonResource
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
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => (string) $this->resource->id,
            'uuid' => (string) $this->resource->id, // TODO: - Remove after 1.9.0
            'type' => 'reviews',
            'href' => '',
        ];
    }
}
