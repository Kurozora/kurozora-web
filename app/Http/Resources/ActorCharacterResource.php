<?php

namespace App\Http\Resources;

use App\ActorCharacter;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorCharacterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var ActorCharacter $actorCharacter */
        $actorCharacter = $this->resource;

        return [
            'actor'         => ActorResource::make($actorCharacter->actor),
            'character'     => CharacterResource::make($actorCharacter->character),
        ];
    }
}
