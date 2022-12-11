<?php

namespace App\Http\Resources;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Character|int $resource
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
        return [
            'id'            => $this->resource?->id ?? $this->resource,
            'type'          => 'characters',
            'href'          => route('api.characters.details', $this->resource, false),
        ];
    }
}
