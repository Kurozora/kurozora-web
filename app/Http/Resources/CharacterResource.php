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

        if ($includeInput = $request->input('include')) {
            // Include relation propagates to nested Resource objects.
            // To avoid loading unnecessary relations, we set it to
            // an empty value.
            $request->merge(['include' => '']);
            $includes = array_unique(explode(',', $includeInput));

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'people' => array_merge($relationships, $this->getPeopleRelationship()),
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
     * Returns the people relationship for the resource.
     *
     * @return array
     */
    protected function getPeopleRelationship(): array
    {
        return [
            'people' => [
                'href' => route('api.characters.people', $this->resource, false),
                'data' => PersonResource::collection($this->resource->people)
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
            'shows' => [
                'href' => route('api.characters.literatures', $this->resource, false),
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
            'shows' => [
                'href' => route('api.characters.games', $this->resource, false),
                'data' => GameResourceBasic::collection($this->resource->games)
            ]
        ];
    }
}
