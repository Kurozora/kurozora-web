<?php

namespace App\Http\Resources;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Person|int $resource
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
            'id' => (string) ($this->resource?->id ?? $this->resource),
            'uuid' => (string) ($this->resource?->id ?? $this->resource), // TODO: - Remove after 1.9.0
            'type' => 'people',
            'href' => route('api.people.details', $this->resource, false),
        ];
    }
}
