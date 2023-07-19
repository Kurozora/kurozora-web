<?php

namespace App\Http\Resources;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Person $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = PersonResourceBasic::make($this->resource)->toArray($request);

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
        return [
            'characters' => [
                'href' => route('api.people.characters', $this->resource, false),
                'data' => CharacterResourceBasic::collection($this->resource->getCharacters(Person::MAXIMUM_RELATIONSHIPS_LIMIT))
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
        return [
            'shows' => [
                'href' => route('api.people.anime', $this->resource, false),
                'data' => AnimeResourceBasic::collection($this->resource->getAnime(Person::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
