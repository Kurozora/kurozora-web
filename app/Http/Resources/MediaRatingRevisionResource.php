<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\MediaRatingRevision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRatingRevisionResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaRatingRevision $resource
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
        $resource = MediaRatingRevisionResourceIdentity::make($this->resource)->toArray($request);

        return array_merge($resource, [
            'attributes' => [
                'score' => (float) $this->resource->rating,
                'description' => $this->resource->description,
                'isSpoiler' => (bool) $this->resource->is_spoiler,
                'recommendation' => $this->resource->recommendation?->value,
                'progress' => $this->resource->progress,
                'progressTotal' => $this->progressTotal(),
                'writtenAt' => $this->resource->written_at->timestamp,
            ],
        ]);
    }

    /**
     * Returns the reviewed model's total number of parts.
     *
     * @return null|int
     */
    protected function progressTotal(): ?int
    {
        if (!$this->resource->relationLoaded('mediaRating')) {
            return null;
        }

        $mediaRating = $this->resource->mediaRating;

        if ($mediaRating === null || !$mediaRating->relationLoaded('model')) {
            return null;
        }

        $model = $mediaRating->model;

        return $model instanceof Anime ? $model->episode_count : null;
    }
}
