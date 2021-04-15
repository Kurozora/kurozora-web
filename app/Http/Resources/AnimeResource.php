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
        /** @param Anime $anime */
        $anime = $this->resource;

        $resource = AnimeResourceBasic::make($anime)->toArray($request);

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'actors':
                        $relationships = array_merge($relationships, $this->getActorsRelationship());
                        break;
                    case 'cast':
                        $relationships = array_merge($relationships, $this->getCastRelationship());
                        break;
                    case 'characters':
                        $relationships = array_merge($relationships, $this->getCharactersRelationship());
                        break;
                    case 'genres':
                        $relationships = array_merge($relationships, $this->getGenresRelationship());
                        break;
                    case 'related-shows':
                        $relationships = array_merge($relationships, $this->getRelatedShowsRelationship());
                        break;
                    case 'seasons':
                        $relationships = array_merge($relationships, $this->getSeasonsRelationship());
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
     * Returns the actors relationship for the resource.
     *
     * @return array
     */
    protected function getActorsRelationship(): array
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'actors' => [
                'href' => route('api.anime.actors', $anime, false),
                'data' => ActorResource::collection($anime->getActors(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the cast relationship for the resource.
     *
     * @return array
     */
    protected function getCastRelationship(): array
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'cast' => [
                'href' => route('api.anime.cast', $anime, false),
                'data' => ActorCharacterAnimeResource::collection($anime->getActorCharacterAnime(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'characters' => [
                'href' => route('api.anime.characters', $anime, false),
                'data' => CharacterResourceBasic::collection($anime->getCharacters(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the genres relationship for the resource.
     *
     * @return array
     */
    protected function getGenresRelationship(): array
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'genres' => [
                'href' => route('api.anime.genres', $anime, false),
                'data' => GenreResource::collection($anime->getGenres(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        /** @param Anime $anime */
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
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'seasons' => [
                'href' => route('api.anime.seasons', $anime, false),
                'data' => AnimeSeasonResource::collection($anime->getSeasons(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'studios' => [
                'href' => route('api.anime.studios', $anime, false),
                'data' => StudioResource::collection($anime->getStudios(Anime::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
