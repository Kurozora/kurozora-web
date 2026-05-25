<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Achievement $resource
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
            'type' => 'achievements',
            'attributes' => [
                'name' => $this->resource->name,
                'description' => $this->resource->description,
                'symbol' => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Symbol)),
                'achievedAt' => $this->resource->achieved_at?->timestamp,
            ]
        ];
    }
}
