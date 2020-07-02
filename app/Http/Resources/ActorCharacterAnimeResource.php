<?php

namespace App\Http\Resources;

use App\ActorCharacterAnime;
use App\Enums\CastRole;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorCharacterAnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var ActorCharacterAnime $actorCharacterAnime */
        $actorCharacterAnime = $this->resource;
        $actorCharacter = $actorCharacterAnime->actor_character;

        return [
            'actor'         => ActorResource::make($actorCharacter->actor),
            'character'     => CharacterResource::make($actorCharacter->character),
            'cast_role'     => CastRole::getDescription($actorCharacterAnime->cast_role)
        ];
    }
}
