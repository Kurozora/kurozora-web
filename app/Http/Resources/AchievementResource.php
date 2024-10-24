<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Badge $resource
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
            'uuid' => (string) $this->resource->id, // TODO: - Remove after 1.9.0
            'type' => 'badges',
            'attributes' => [
                'name' => $this->resource->name,
                'description' => $this->resource->description,
                'textColor' => $this->resource->text_color,
                'backgroundColor' => $this->resource->background_color,
                'symbol' => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Symbol)),
                'achieved_at' => $this->resource->achieved_at?->timestamp,
            ]
        ];
    }
}
