<?php

namespace App\Http\Resources;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Theme $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'type'          => 'themes',
            'href'          => route('api.themes.details', $this->resource, false),
        ];
    }
}
