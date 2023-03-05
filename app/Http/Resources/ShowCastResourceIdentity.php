<?php

namespace App\Http\Resources;

use App\Models\AnimeCast;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ShowCastResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var AnimeCast $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id'            => $this->resource->id,
            'uuid'          => (string) $this->resource->id,
            'type'          => 'cast',
            'href'          => route('api.show-cast.details', $this->resource, false),
        ];
    }
}
