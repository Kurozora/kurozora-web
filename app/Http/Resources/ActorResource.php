<?php

namespace App\Http\Resources;

use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Actor $actor */
        $actor = $this->resource;

        $resource = ActorResourceBasic::make($actor)->toArray($request);

        if($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'characters':
                        $relationships = array_merge($relationships, $this->getCharactersRelationship());
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
     * Returns the characters relationship for the resource.
     *
     * @return array
     */
    protected function getCharactersRelationship(): array
    {
        /** @param Actor $actor */
        $actor = $this->resource;

        return [
            'characters' => [
                'href' => route('api.actors.characters', $actor, false),
                'data' => CharacterResourceBasic::collection($actor->getCharacters(Actor::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        /** @param Actor $actor */
        $actor = $this->resource;

        return [
            'shows' => [
                'href' => route('api.actors.anime', $actor, false),
                'data' => AnimeResourceBasic::collection($actor->getAnime(Actor::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
