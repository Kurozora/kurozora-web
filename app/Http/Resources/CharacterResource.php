<?php

namespace App\Http\Resources;

use App\Models\Character;
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

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'people' => array_merge($relationships, $this->getPeopleRelationship()),
                    'shows' => array_merge($relationships, $this->getAnimeRelationship()),
                    default => $relationships,
                };
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns the people relationship for the resource.
     *
     * @return array
     */
    protected function getPeopleRelationship(): array
    {
        /** @vra Character $character */
        $character = $this->resource;

        return [
            'people' => [
                'href' => route('api.characters.people', $character, false),
                'data' => PersonResource::collection($character->getPeople(Character::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        /** @var Character $character */
        $character = $this->resource;

        return [
            'shows' => [
                'href' => route('api.characters.anime', $character, false),
                'data' => AnimeResourceBasic::collection($character->getAnime(Character::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
