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

        if ($includeInput = $request->input('include')) {
            // Include relation propagates to nested Resource objects.
            // To avoid loading unnecessary relations, we set it to
            // an empty value.
            $request->merge(['include' => '']);
            $includes = array_unique(explode(',', $includeInput));

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'characters' => array_merge($relationships, $this->getCharactersRelationship()),
                    'shows' => array_merge($relationships, $this->getAnimeRelationship()),
                    'literatures' => array_merge($relationships, $this->getMangaRelationship()),
                    'games' => array_merge($relationships, $this->getGamesRelationship()),
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
                'data' => CharacterResourceBasic::collection($this->resource->characters)
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
                'data' => AnimeResourceBasic::collection($this->resource->anime)
            ]
        ];
    }

    /**
     * Returns the manga relationship for the resource.
     *
     * @return array
     */
    protected function getMangaRelationship(): array
    {
        return [
            'literatures' => [
                'href' => route('api.people.literatures', $this->resource, false),
                'data' => LiteratureResourceBasic::collection($this->resource->manga)
            ]
        ];
    }

    /**
     * Returns the games relationship for the resource.
     *
     * @return array
     */
    protected function getGamesRelationship(): array
    {
        return [
            'games' => [
                'href' => route('api.people.games', $this->resource, false),
                'data' => GameResourceBasic::collection($this->resource->games)
            ]
        ];
    }
}
