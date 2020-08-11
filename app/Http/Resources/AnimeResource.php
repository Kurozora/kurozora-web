<?php

namespace App\Http\Resources;

use App\Anime;
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

        if($request->input('include')) {
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
    protected function getActorsRelationship() {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'actors' => [
                'data' => ActorResource::collection($anime->getActors(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.actors', $anime, false)
            ]
        ];
    }

    /**
     * Returns the cast relationship for the resource.
     *
     * @return array
     */
    protected function getCastRelationship()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'cast' => [
                'data' => ActorCharacterAnimeResource::collection($anime->getActorCharacterAnime(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.cast', $anime, false)
            ]
        ];
    }

    /**
     * Returns the characters relationship for the resource.
     *
     * @return array
     */
    protected function getCharactersRelationship()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'characters' => [
                'data' => CharacterResource::collection($anime->getCharacters(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.characters', $anime, false)
            ]
        ];
    }

    /**
     * Returns the genres relationship for the resource.
     *
     * @return array
     */
    protected function getGenresRelationship()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'genres' => [
                'data' => GenreResource::collection($anime->getGenres(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.genres', $anime, false)
            ]
        ];
    }

    /**
     * Returns the related-shows relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedShowsRelationship()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'relatedShows' => [
                'data' => AnimeRelatedShowsResource::collection($anime->getAnimeRelations(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.related-shows', $anime, false)
            ]
        ];
    }

    /**
     * Returns the seasons relationship for the resource.
     *
     * @return array
     */
    protected function getSeasonsRelationship() {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'seasons' => [
                'data' => AnimeSeasonResource::collection($anime->getSeasons(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.seasons', $anime, false)
            ]
        ];
    }

    /**
     * Returns the studios relationship for the resource.
     *
     * @return array
     */
    protected function getStudiosRelationship()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'studios' => [
                'data' => StudioResource::collection($anime->getStudios(Anime::MAXIMUM_RELATED_SHOWS_LIMIT)),
                'href' => route('api.anime.studios', $anime, false)
            ]
        ];
    }
}
