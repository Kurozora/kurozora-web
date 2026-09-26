<?php

namespace App\Http\Resources;

use App\Models\PersonRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonRelationshipResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var PersonRelationship $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'id' => (string) $this->resource->id,
            'type' => 'relationships',
            'attributes' => [
                'relation' => $this->resource->type->description,
                'startedAt' => $this->resource->started_on?->toDateString(),
                'endedAt' => $this->resource->ended_on?->toDateString(),
            ],
        ];

        $relationships = [
            'people' => [
                'href' => route('api.people.details', $this->resource->relatedPerson, false),
                'data' => PersonResourceBasic::collection([$this->resource->relatedPerson]),
            ],
        ];

        return array_merge($resource, ['relationships' => $relationships]);
    }
}
