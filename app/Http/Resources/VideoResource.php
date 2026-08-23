<?php

namespace App\Http\Resources;

use App\Models\Game;
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
        $resource = VideoResourceIdentity::make($this->resource)->toArray($request);

        $resource = array_merge($resource, [
            'attributes' => [
                'code' => $this->resource->code,
                'source' => $this->resource->source,
                'url' => $this->resource->getUrl(),
                'isSub' => $this->resource->is_sub,
                'isDub' => $this->resource->is_dub,
                'type' => $this->resource->type->description,
                'publishedAt' => $this->resource->published_at?->timestamp,
                'viewCount' => $this->resource->view_count,
            ],
        ]);

        if ($this->resource->relationLoaded('videoable') && !empty($this->resource->videoable)) {
            $resource = array_merge($resource, [
                'relationships' => $this->getParentRelationship(),
            ]);
        }

        return $resource;
    }

    /**
     * Returns the identity of the title the video belongs to.
     *
     * @return array
     */
    protected function getParentRelationship(): array
    {
        $parent = $this->resource->videoable;

        return [
            'parent' => [
                'data' => $parent instanceof Game
                    ? GameResourceIdentity::collection([$parent])
                    : AnimeResourceIdentity::collection([$parent]),
            ],
        ];
    }
}
