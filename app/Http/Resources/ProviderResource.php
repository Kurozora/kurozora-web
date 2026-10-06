<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Provider $resource
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
            'type' => 'providers',
            'attributes' => [
                'slug' => $this->resource->slug,
                'name' => $this->resource->original_name,
                'alternativeNames' => $this->resource->alternative_names,
                'url' => $this->resource->url,
                'logo' => MediaResource::make(
                    $this->resource->media->where('collection_name', '=', MediaCollection::Logo)
                        ->sortBy('order_column')
                        ->first()
                ),
                'isPublic' => (bool) $this->resource->is_public,
            ],
        ];
    }
}
