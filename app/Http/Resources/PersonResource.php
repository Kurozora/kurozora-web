<?php

namespace App\Http\Resources;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Person $person */
        $person = $this->resource;

        $resource = PersonResourceBasic::make($person)->toArray($request);

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'characters' => array_merge($relationships, $this->getCharactersRelationship()),
                    'shows' => array_merge($relationships, $this->getAnimeRelationship()),
                    default => $relationships,
                };
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
        /** @var Person $person */
        $person = $this->resource;

        return [
            'characters' => [
                'href' => route('api.people.characters', $person, false),
                'data' => CharacterResourceBasic::collection($person->getCharacters(Person::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        /** @var Person $person */
        $person = $this->resource;

        return [
            'shows' => [
                'href' => route('api.people.anime', $person, false),
                'data' => AnimeResourceBasic::collection($person->getAnime(Person::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
