<?php

namespace App\Http\Resources;

use App\Models\MediaStudio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /** @var MediaStudio $mediaStudio */
        $mediaStudio = $this->resource;

        $resource = [
            'id'            => $mediaStudio->id,
            'uuid'          => (string) $mediaStudio->id,
            'type'          => 'studios',
            'href'          => route('api.anime.studios', $mediaStudio->anime, false),
            'attributes'    => $mediaStudio->only(['is_licensor', 'is_producer', 'is_studio']),
        ];

        $relationships = [];

        $relationships = array_merge($relationships, $this->getStudioRelationship());

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the person relationship for the resource.
     *
     * @return array
     */
    protected function getStudioRelationship(): array
    {
        /** @var MediaStudio $mediaStudio */
        $mediaStudio = $this->resource;

        return [
            'studio' => [
                'href' => route('api.studios.details', $mediaStudio, false),
                'data' => StudioResourceBasic::make($mediaStudio->studio),
            ]
        ];
    }
}
