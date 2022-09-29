<?php

namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Video $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'source' => $this->resource->source,
            'url' => $this->resource->getUrl(),
            'isSub' => $this->resource->is_sub,
            'isDub' => $this->resource->is_dub,
            'type' => $this->resource->type->description,
        ];
    }
}
