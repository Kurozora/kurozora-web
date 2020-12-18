<?php

namespace App\Http\Resources;

use App\Models\ActorCharacterAnime;
use App\Enums\CastRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorCharacterAnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ActorCharacterAnime $actorCharacterAnime */
        $actorCharacterAnime = $this->resource;

        $resource = [
            'type'          => 'cast',
            'href'          => route('api.anime.cast', $actorCharacterAnime, false),
            'attributes'    => [
                'role' => CastRole::getDescription($actorCharacterAnime->cast_role)
            ]
        ];

        $relationships = [];

        // Include actors
        $relationships = array_merge($relationships, $this->getActorsRelationship());

        // Include characters
        $relationships = array_merge($relationships, $this->getCharactersRelationship());

        // Merge relationships with resource
        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the actors relationship for the resource.
     *
     * @return array
     */
    protected function getActorsRelationship(): array
    {
        /** @param ActorCharacterAnime $actorCharacterAnime */
        $actorCharacterAnime = $this->resource;

        $actorCharacter = $actorCharacterAnime->actor_character;

        return [
            'actors' => [
                'data' => ActorResource::collection([$actorCharacter->actor])
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
        /** @param ActorCharacterAnime $actorCharacterAnime */
        $actorCharacterAnime = $this->resource;

        $actorCharacter = $actorCharacterAnime->actor_character;

        return [
            'characters' => [
                'data' => CharacterResourceBasic::collection([$actorCharacter->character])
            ]
        ];
    }
}
