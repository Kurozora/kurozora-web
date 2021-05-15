<?php

namespace App\Http\Resources;

use App\Models\AnimeStudio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AnimeStudio $animeStudio */
        $animeStudio = $this->resource;

        $resource = [
            'id'            => $animeStudio->id,
            'type'          => 'studios',
            'href'          => route('api.anime.studios', $animeStudio->anime, false),
            'attributes'    => $animeStudio->only(['is_licensor', 'is_producer', 'is_studio']),
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
        /** @var AnimeStudio $animeStudio */
        $animeStudio = $this->resource;

        return [
            'studio' => [
                'href' => route('api.studios.details', $animeStudio, false),
                'data' => StudioResourceBasic::make($animeStudio->studio),
            ]
        ];
    }
}
