<?php

namespace App\Http\Resources;

use App\Models\AnimeCast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeCastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AnimeCast $animeCast */
        $animeCast = $this->resource;

        $resource = [
            'id'            => $animeCast->id,
            'type'          => 'cast',
            'href'          => route('api.anime.cast', $animeCast, false),
            'attributes'    => [
                'role'      => $animeCast->cast_role->only(['name', 'description']),
                'language'  => $animeCast->language->only('name'),
            ]
        ];

        $relationships = [];

        // Include people
        $relationships = array_merge($relationships, $this->getPeopleRelationship());

        // Include characters
        $relationships = array_merge($relationships, $this->getCharactersRelationship());

        // Merge relationships with resource
        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns the people relationship for the resource.
     *
     * @return array
     */
    protected function getPeopleRelationship(): array
    {
        /** @var AnimeCast $animeCast */
        $animeCast = $this->resource;

        return [
            'people' => [
                'data' => PersonResource::collection([$animeCast->person])
            ]
        ];
    }

    /**
     * Returns the characters relationship for the resource.
     *
     * @return array
     */
    protected function getCharactersRelationship(): array
    {
        /** @var AnimeCast $animeCast */
        $animeCast = $this->resource;

        return [
            'characters' => [
                'data' => CharacterResourceBasic::collection([$animeCast->character])
            ]
        ];
    }
}
