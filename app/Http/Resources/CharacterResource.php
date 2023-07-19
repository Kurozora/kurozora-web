<?php

namespace App\Http\Resources;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Character $resource
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
        $resource = CharacterResourceBasic::make($this->resource)->toArray($request);

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
        return [
            'people' => [
                'href' => route('api.characters.people', $this->resource, false),
                'data' => PersonResource::collection($this->resource->getPeople(Character::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'href' => route('api.characters.anime', $this->resource, false),
                'data' => AnimeResourceBasic::collection($this->resource->getAnime(Character::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
