<?php

namespace App\Http\Resources;

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
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'type'          => 'themes',
            'href'          => route('api.themes.details', $this->resource, false),
            'attributes'        => [
                'screenshot'    => ImageResource::make($this->resource->screenshot_image),
                'name'          => $this->resource->name,
                'downloadLink'  => route('api.themes.download', ['theme' => $this->resource->id])
            ]
        ];
    }
}
