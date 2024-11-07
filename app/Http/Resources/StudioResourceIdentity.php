<?php

namespace App\Http\Resources;

use App\Models\Studio;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class StudioResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Studio|int $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => (string) ($this->resource?->id ?? $this->resource),
            'type' => 'studios',
            'href' => route('api.studios.details', $this->resource, false),
        ];
    }
}
