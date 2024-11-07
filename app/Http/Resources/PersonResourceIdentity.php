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
            'type' => 'people',
            'href' => route('api.people.details', $this->resource, false),
        ];
    }
}
