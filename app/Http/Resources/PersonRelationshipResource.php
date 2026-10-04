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
        return [
            'person' => PersonResourceBasic::make($this->resource->relatedPerson),
            'attributes' => [
                'relation' => [
                    'name' => $this->resource->type->key,
                    'description' => $this->resource->type->description,
                ],
                'startedAt' => $this->resource->started_on?->timestamp,
                'endedAt' => $this->resource->ended_on?->timestamp,
            ],
        ];
    }
}
