<?php

namespace App\Http\Resources;

use App\Models\MediaLanguage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaLanguageResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaLanguage $resource
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
            'name' => $this->resource->language->name,
            'code' => $this->resource->language->code,
            'iso6393' => $this->resource->language->iso_639_3,
            'type' => $this->resource->type->value,
            'isOriginal' => $this->resource->language->code === $this->resource->model?->originLanguage(),
        ];
    }
}
