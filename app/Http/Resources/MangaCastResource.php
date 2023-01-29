<?php

namespace App\Http\Resources;

use App\Models\MangaCast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MangaCastResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MangaCast $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = LiteratureResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes'    => [
                'role'      => $this->resource->cast_role->only(['name', 'description'])
            ]
        ]);

        $relationships = [];

        // Include characters
        $relationships = array_merge($relationships, $this->getCharactersRelationship());

        // Merge relationships with resource
        return array_merge($resource, ['relationships' => $relationships]);
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
