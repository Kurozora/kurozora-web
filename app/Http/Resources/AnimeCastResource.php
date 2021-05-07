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

        // Include actors
        $relationships = array_merge($relationships, $this->getActorsRelationship());

        // Include characters
        $relationships = array_merge($relationships, $this->getCharactersRelationship());

        // Merge relationships with resource
        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the actors relationship for the resource.
     *
     * @return array
     */
    protected function getActorsRelationship(): array
    {
        /** @var AnimeCast $animeCast */
        $animeCast = $this->resource;

        return [
            'actors' => [
                'data' => ActorResource::collection([$animeCast->actor])
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
