<?php

namespace App\Http\Resources;

use App\Anime;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        $resource = AnimeResourceBasic::make($anime)->toArray($request);

        if($request->input('include')) {
            $includes = explode(',', $request->input('include'));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'actors':
                        $relationships = array_merge($relationships, $this->getActorsRelationship());
                        break;
                    case 'cast':
                        $relationships = array_merge($relationships, $this->getActorCharacterAnime());
                        break;
                    case 'characters':
                        $relationships = array_merge($relationships, $this->getCharactersRelationship());
                        break;
                    case 'relations':
                        $relationships = array_merge($relationships, $this->getRelationsRelationship());
                        break;
                    case 'seasons':
                        $relationships = array_merge($relationships, $this->getSeasonsRelationship());
                        break;
                }
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
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
                'data' => AnimeSeasonResource::collection($anime->getSeasons(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => route('anime.seasons', $anime, false)
            ]
        ];
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
                'data' => ActorResource::collection($anime->getActors(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => route('anime.actors', $anime, false)
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
                'data' => CharacterResource::collection($anime->getCharacters(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => route('anime.characters', $anime, false)
            ]
        ];
    }

    /**
     * Returns the relations relationship for the resource.
     *
     * @return array
     */
    protected function getRelationsRelationship()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'relations' => [
                'data' => AnimeRelationsResource::collection($anime->getAnimeRelations(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => route('anime.relations', $anime, false)
            ]
        ];
    }

    /**
     * Returns the cast relationship for the resource.
     *
     * @return array
     */
    protected function getActorCharacterAnime()
    {
        /** @param Anime $anime */
        $anime = $this->resource;

        return [
            'cast' => [
                'data' => ActorCharacterAnimeResource::collection($anime->getActorCharacterAnime(Anime::MAXIMUM_RELATIONSHIP_LIMIT)),
                'href' => route('anime.cast', $anime, false)
            ]
        ];
    }
}
