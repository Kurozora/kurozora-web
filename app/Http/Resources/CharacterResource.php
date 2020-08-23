<?php

namespace App\Http\Resources;

use App\Character;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Character $character */
        $character = $this->resource;

        $resource = CharacterResourceBasic::make($character)->toArray($request);

        if($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'actors':
                        $relationships = array_merge($relationships, $this->getActorsRelationship());
                        break;
                    case 'shows':
                        $relationships = array_merge($relationships, $this->getAnimeRelationship());
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
        /** @param Character $character */
        $character = $this->resource;

        return [
            'actors' => [
                'href' => route('api.characters.actors', $character, false),
                'data' => ActorResource::collection($character->getActors(Character::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the anime relationship for the resource.
     *
     * @return array
     */
    protected function getAnimeRelationship(): array
    {
        /** @param Character $character */
        $character = $this->resource;

        return [
            'shows' => [
                'href' => route('api.characters.anime', $character, false),
                'data' => AnimeResourceBasic::collection($character->getAnime(Character::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
