<?php

namespace App\Http\Resources;

use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        $resource = AnimeResourceBasic::make($anime)->toArray($request);

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'cast':
                        $relationships = array_merge($relationships, $this->getCastRelationship());
                        break;
                    case 'characters':
                        $relationships = array_merge($relationships, $this->getCharactersRelationship());
                        break;
                    case 'related-shows':
                        $relationships = array_merge($relationships, $this->getRelatedShowsRelationship());
                        break;
                    case 'seasons':
                        $relationships = array_merge($relationships, $this->getSeasonsRelationship());
                        break;
                    case 'staff':
                        $relationships = array_merge($relationships, $this->getStaffRelationship());
                        break;
                    case 'studios':
                        $request->merge(['include' => 'shows']);
                        $request->merge(['anime' => $anime]);
                        $relationships = array_merge($relationships, $this->getStudiosRelationship());
                        break;
                }
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns the cast relationship for the resource.
     *
     * @return array
     */
    protected function getCastRelationship(): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        return [
            'cast' => [
                'href' => route('api.anime.cast', $anime, false),
                'data' => AnimeCastResource::collection($anime->getCast(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the characters relationship for the resource.
     *
     * @return array
     */
    protected function getCharactersRelationship(): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        return [
            'characters' => [
                'href' => route('api.anime.characters', $anime, false),
                'data' => CharacterResourceBasic::collection($anime->getCharacters(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the related-shows relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedShowsRelationship(): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        return [
            'relatedShows' => [
                'href' => route('api.anime.related-shows', $anime, false),
                'data' => AnimeRelatedShowsResource::collection($anime->getAnimeRelations(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the seasons relationship for the resource.
     *
     * @return array
     */
    protected function getSeasonsRelationship(): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        return [
            'seasons' => [
                'href' => route('api.anime.seasons', $anime, false),
                'data' => SeasonResource::collection($anime->getSeasons(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the people relationship for the resource.
     *
     * @return array
     */
    protected function getStaffRelationship(): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        return [
            'staff' => [
                'href' => route('api.anime.staff', $anime, false),
                'data' => AnimeStaffResource::collection($anime->getStaff(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the studios relationship for the resource.
     *
     * @return array
     */
    protected function getStudiosRelationship(): array
    {
        /** @var Anime $anime */
        $anime = $this->resource;

        return [
            'studios' => [
                'href' => route('api.anime.studios', $anime, false),
                'data' => AnimeStudioResource::collection($anime->getAnimeStudios(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
