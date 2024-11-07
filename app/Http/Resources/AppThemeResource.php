<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\AppTheme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppThemeResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var AppTheme $resource
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
            'type' => 'themes',
            'href' => route('api.theme-store.details', $this->resource, false),
            'attributes' => [
                'screenshots' => MediaResource::collection($this->resource->media->where('collection_name', '=', MediaCollection::Screenshot)),
                'name' => $this->resource->name,
                'downloadLink' => route('api.theme-store.download', ['appTheme' => $this->resource->id]),
                'downloadCount' => $this->resource->download_count,
                'version' => $this->resource->version,
            ]
        ];
    }
}
