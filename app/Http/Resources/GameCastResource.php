<?php

namespace App\Http\Resources;

use App\Models\GameCast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameCastResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var GameCast $resource
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
        $resource = GameCastResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes'    => [
                'role'      => $this->resource->castRole->only(['name', 'description']),
                'language'  => $this->resource->language?->name,
            ]
        ]);

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
        // Since some cast can only contain a character, check if the person exists before creating a collection.
        $people = [];
        if ($this->resource->person) {
            $people[] = $this->resource->person;
        }

        return [
            'people' => [
                'data' => PersonResource::collection($people)
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
        return [
            'characters' => [
                'data' => CharacterResourceBasic::collection([$this->resource->character])
            ]
        ];
    }
}
