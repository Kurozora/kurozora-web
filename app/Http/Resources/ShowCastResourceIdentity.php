<?php

namespace App\Http\Resources;

use App\Models\AnimeCast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->id,
            'type' => 'show-cast',
            'href' => route('api.show-cast.details', $this->resource, false),
        ];
    }
}
