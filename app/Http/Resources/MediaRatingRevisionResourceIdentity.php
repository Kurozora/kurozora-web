<?php

namespace App\Http\Resources;

use App\Models\MediaRatingRevision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRatingRevisionResourceIdentity extends JsonResource
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
        return [
            'id' => (string) $this->resource->id,
            'type' => 'review-revisions',
            'href' => '',
        ];
    }
}
